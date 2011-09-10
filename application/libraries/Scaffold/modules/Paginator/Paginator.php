<?php

class Paginator
{
  static public function flag()
  {
    Scaffold::flag_set('paginator');
    Scaffold::flag_set(sprintf('pagestart=%d', @$_GET['pageStart']));
    Scaffold::flag_set(sprintf('pageend=%d', @$_GET['pageEnd']));
  }
  
  static public function output()
  {
    //$tmp = microtime(true);
    
    $currentStart = (int) @$_GET['pageStart'];
    $currentEnd = (int) @$_GET['pageEnd'];
    $selectorsPerPage = 4000;
    
    if( !$currentStart && !$currentEnd ) {
      $cssLength = strlen(Scaffold::$output);
      $cssSelectors = self::_getSelectorCount();
      $avgLengthPerSelector = (int) round($cssLength / $cssSelectors);
      
      // Segment the file
      $segments = array();
      $currentStart = 0;
      $currentPos = 0;
      $currentCount = 0;
      $i = 0;

      do {
        // Get next pos
        $currentPos += round( $selectorsPerPage * $avgLengthPerSelector );
        if( $currentPos > $cssLength ) {
          $currentPos = $cssLength;
        } else {
          // Rewind until it's less than selector count
          do {
            $currentPos = round($currentPos - (5 * $avgLengthPerSelector)); // Fudgesicles
            $currentPos = self::_getPreviousSelectorEnd($currentPos - $cssLength, $currentStart);
            $currentCount = self::_getSelectorCount($currentStart, $currentPos - $currentStart);
          } while( $currentCount > $selectorsPerPage );
        }
        
        // Cleanup?
        $segments[/*$currentStart*/] = $currentPos;
        $currentStart = $currentPos;
        $i++;
        
      } while( $currentCount > 0 && $currentPos < $cssLength && $i < 100 );

      // Only do stuff if there is more than one segment
      if( count($segments) > 1 ) {
        // Truncate CSS?
        $initialSegmentEnd = array_shift($segments);
        Scaffold::$output = substr(Scaffold::$output, 0, $initialSegmentEnd + 1);

        // Generate imports
        $urlInfo = parse_url($_SERVER['REQUEST_URI']);
        $urlQueryArr = array();
        parse_str($urlInfo['query'], $urlQueryArr);

        $importStr = '';
        $lastEnd = $initialSegmentEnd;
        foreach( $segments as $segmentEnd ) {
          $urlQueryArr['pageStart'] = $lastEnd + 1;
          $urlQueryArr['pageEnd'] = $segmentEnd + 1;
          $url = $urlInfo['path'] . '?' . http_build_query($urlQueryArr);

          $importStr .= '@import "' . $url . '";' . "\r\n"; // . "\r\n";
          
          $lastEnd = $segmentEnd;
        }
        
        Scaffold::$output = $importStr . "\r\n" . "\r\n" . Scaffold::$output;
        //Scaffold::$output .= "\r\n" . "\r\n" . $importStr;
      }
    }

    // Just output substr

    else {
      Scaffold::$output = substr(Scaffold::$output, $currentStart, $currentEnd - $currentStart);
    }
    
    //var_dump(microtime(true) - $tmp);
  }

  static protected function _getSelectorCount($start = 0, $length = null)
  {
    if( null === $length ) {
      $length = strlen(Scaffold::$output) - $start;
    }
    return substr_count(Scaffold::$output, '{', $start, $length) +
        substr_count(Scaffold::$output, ',', $start, $length);
  }

  static protected function _getPreviousSelectorEnd($offset = null, $start = null)
  {
    $pos = strrpos(Scaffold::$output, '}', $offset);
    if( false === $pos ) {
      return false;
    } 
    //$pos += 1;
    if( $pos < $start ) {
      return false;
    } else {
      return $pos;
    }
  }
}
