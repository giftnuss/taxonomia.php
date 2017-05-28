<?php

namespace Siox;

use Siox\Blob\Storage;

class Blob implements Storage
{
    protected $storage;

    public function __construct(Storage $storage=null)
    {
        if($storage !== null) {
            $this->setStorage( $storage );
        }
    }

    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function retrieve($key, $version=null)
    {
        return $this->storage->retrieve($key);
    }

    public function store($key,$value,$version=null)
    {
        return $this->storage->store($key,$value);
    }

}

/**
 * W3C Blob Interface in IDL
[Constructor(optional sequence<BlobPart> blobParts, optional BlobPropertyBag options),
Exposed=(Window,Worker)]
interface Blob {

  readonly attribute unsigned long long size;
  readonly attribute DOMString type;

  // slice Blob into byte-ranged chunks
  Blob slice([Clamp] optional long long start,
            [Clamp] optional long long end,
            optional DOMString contentType);
};

dictionary BlobPropertyBag {
  DOMString type = "";
};

typedef (BufferSource or Blob or USVString) BlobPart;

[Constructor(sequence<BlobPart> fileBits,
             USVString fileName,
             optional FilePropertyBag options),
Exposed=(Window,Worker)]
interface File : Blob {
  readonly attribute DOMString name;
  readonly attribute long long lastModified;
};

dictionary FilePropertyBag : BlobPropertyBag {
  long long lastModified;
};

[Exposed=(Window,Worker)]
interface FileList {
  getter File? item(unsigned long index);
  readonly attribute unsigned long length;
};

[Constructor, Exposed=(Window,Worker)]
interface FileReader: EventTarget {

  // async read methods
  void readAsArrayBuffer(Blob blob);
  void readAsBinaryString(Blob blob);
  void readAsText(Blob blob, optional DOMString label);
  void readAsDataURL(Blob blob);

  void abort();

  // states
  const unsigned short EMPTY = 0;
  const unsigned short LOADING = 1;
  const unsigned short DONE = 2;


  readonly attribute unsigned short readyState;

  // File or Blob data
  readonly attribute (DOMString or ArrayBuffer)? result;

  readonly attribute DOMException? error;

  // event handler content attributes
  attribute EventHandler onloadstart;
  attribute EventHandler onprogress;
  attribute EventHandler onload;
  attribute EventHandler onabort;
  attribute EventHandler onerror;
  attribute EventHandler onloadend;

};

[Constructor, Exposed=(DedicatedWorker,SharedWorker)]
interface FileReaderSync {
  // Synchronously return strings

  ArrayBuffer readAsArrayBuffer(Blob blob);
  DOMString readAsBinaryString(Blob blob);
  DOMString readAsText(Blob blob, optional DOMString label);
  DOMString readAsDataURL(Blob blob);
};

[Exposed=(Window,DedicatedWorker,SharedWorker)]
partial interface URL {
  static DOMString createObjectURL(Blob blob);
  static void revokeObjectURL(DOMString url);
};

*/
