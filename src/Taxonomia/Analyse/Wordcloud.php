<?php

namespace Taxonomia\Analyse;

use StopWordFactory;
use TextAnalysis\Tokenizers\GeneralTokenizer;
use TextAnalysis\Analysis\FreqDist;
use TextAnalysis\Filters\QuotesFilter;
use TextAnalysis\Filters\StopWordsFilter;
use TextAnalysis\Filters\PunctuationFilter;
use TextAnalysis\Filters\NumbersFilter;

class Wordcloud
{
    protected $text;
    protected $language;

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function setLanguage($lang)
    {
        $this->language = $lang;
        return $this;
    }

    public function getStopwords()
    {
        $filter = array(
            'german' => "stop-words_german_1_de.txt",
            'english' => "stop-words_english_6_en.txt"
        );
        if(isset($filter[$this->language])) {
            return StopWordFactory::get($filter[$this->language]);
        }
        return [];
    }

    public function getCloud()
    {
        $tokenizer = new GeneralTokenizer();
        $tokens = $tokenizer->tokenize($this->text);

        $stopwords = $this->getStopwords();
        $stopwordfilter = new StopWordsFilter($stopwords);
        $quotesfilter = new QuotesFilter();
        $numbers = new NumbersFilter();

        $tokens = array_map(function ($word)
            use ($quotesfilter){
            return $quotesfilter->transform($word);
        },$tokens);

        $tokens =  array_filter($tokens, function ($word)
            use ($stopwordfilter) {
            return $stopwordfilter->transform(strtolower($word)) !== null;
        });

        $tokens =  array_filter($tokens, function ($word)
            use ($numbers) {
            return $numbers->transform($word) !== '';
        });

        $freqDist = new FreqDist($tokens);

        $map = $freqDist->getKeyValuesByFrequency();
        $chunks = array_chunk($map,100,true);
        $cloud = [];
        foreach($chunks[0] as $k => $v) {
            $cloud[] = [$k,$v];
        }
        return $cloud;
    }
}
