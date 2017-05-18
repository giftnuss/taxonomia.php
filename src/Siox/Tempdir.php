<?php

namespace Siox;

use Exception;

class Tempdir
{
  /**
   * the directory where the temporary directory resides
   */
  protected $_tempbase;
  /**
   * the full path to the temporary directory
   */
  protected $_path;

  protected $removeOnDestruct = true;

  protected $debug = false;

  public function __construct($basepath = null, array $options)
  {
      if($basepath) {
          $this->setBasepath($basepath);
      }
      $options = array_merge([
          'debug' => false,
          'remove' => true
        ], $options);

      $this->removeOnDestruct($options['remove']);
      $this->debug = $options['debug'];
      $this->_createTempDir();
  }

  public function setBasepath($path)
  {
      if($path === null) {
          $path = sys_get_temp_dir();
      }
      if(is_dir($path)) {
        if(is_writable($path)) {
          if(!(substr($path,-1) == '/' || substr($path,-1) == DIRECTORY_SEPARATOR)) {
              $path .= DIRECTORY_SEPARATOR;
          }
          $this->_tempbase = $path;
        }
        else {
          throw new Exception("Temporäres Basisverzeichnis '$path' nicht schreibbar.");
        }
      }
      else {
        throw new Exception("Temporäres Basisverzeichnis '$path' existiert nicht.");
      }
  }

  public function removeOnDestruct($remove)
  {
      $this->removeOnDestruct = $remove;
  }

  public function getTempdir()
  {
      return $this->_path;
  }

  protected function _createTempDir()
  {
      $base = $this->_tempbase;
      do {
        $dir = $base . $this->createTempName();
      } while(is_dir($dir));

      if(!mkdir($dir)) {
        throw new Exception("Temporäres Verzeichnis '$dir' nicht erstellt.");
      }
      $this->_path = $dir . DIRECTORY_SEPARATOR;
    }

    public function getFilehandle($name,$flags = 'rb')
    {
        if(!($file = fopen("{$this->_path}{$name}", $flags))) {
            throw new Exception("Fehler beim Anlegen von '{$this->_path}{$name}'.");
        }
        return $file;
    }

    public function writeFile($name,$content)
    {
        $file = $this->getFilehandle($name, "wb");
        if(FALSE===fwrite($file,$content)) {
            throw new Exception("Fehler beim Schreiben in '{$this->_path}{$name}'.");
        }
        if(!fclose($file)) {
            throw new Exception("Fehler beim Schließen von '{$this->_path}{$name}'.");
        }
    }
    public function readFile($name)
    {
        if(false === ($content=file_get_contents($this->_path . $name))) {
            throw new Exception("Datei $name nicht lesbar.");
        }
        return $content;
    }
    public function writeTempFile($content)
    {
        $filename = $this->createTempName(8) . '.temp';
        $this->writeFile($filename,$content);
        return $filename;
    }
    /**
     * Löscht das Verzeichnis wieder
     * @param - optional - ein Verzeichnis mit SEPARATOR am Ende
     */
    public function remove($dirname=null)
    {
        if(is_null($dirname)) {
            $dirname = $this->_path;
        }
        $dir = opendir($dirname);
        while(FALSE !== ($file=readdir($dir))) {
            if($file=='.' || $file=='..') {
                continue;
            }
            $filename = $dirname . $file;
            if(is_dir($filename)) {
                $filename .= DIRECTORY_SEPARATOR;
                $this->remove($filename);
                rmdir($filename);
            }
            else {
                unlink($filename);
            }
        }
        closedir($dir);
        rmdir($dirname);
    }
    /**
     * Erzeugt einen Verzeichnisnamen
     */
    public function createTempName($leng=8)
    {
      $id = "";
      $chars = "0123456789abcdefghijklmnopqrstuvwxyz";
      $alimit = strlen($chars) - 1;
      for ($i=0;$i<$leng;$i++) {
        $id .= substr($chars,rand(0,$alimit),1);
      }
      return $id;
    }
    /**
     * Führt einen Shellbefehl in dem Verzeichnis aus.
     */
    public function runCommand($cmd)
    {
        $this->writeFile("command.txt",$cmd);
        $save = getcwd();
        if(!chdir($this->_path)) {
            throw new Exception("Wechseln in Verzeichnis {$this->_path} nicht möglich.");
        }
        $result = (system($cmd) == 0);
        if(!chdir($save)) {
            throw new Exception("Verzeichnis wechseln zurück in {$save} nicht möglich.");
        }
        return $result;
    }
    /**
     * Kopiert eine Datei mit (copy) in das Verzeichnis
     */
    public function fetchFile($source,$targetname=null)
    {
        if($targetname === null) {
           $targetname = basename($source);
        }
        if(!is_readable($source)) {
            throw new Exception("Quelldatei {$source} kann nicht gelesen werden.");
        }
        $targetfile = $this->_path . $targetname;
        return copy($source,$targetfile);
    }

    public function __destruct()
    {
        if($this->removeOnDestruct && is_dir($this->_path)) {
            $this->remove();
        }
    }

    public static function systemTempdir()
    {
      $candidates = array(getenv('TMPDIR'),getenv('TEMP'),getenv('TMP'),'/tmp');
      foreach($candidates as $c) {
        if(is_dir($c) && is_writable($c)) {
          return $c;
        }
      }
      return false;
    }
}
