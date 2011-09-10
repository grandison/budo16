<?php

/**
 * Absolute_Urls
 *
 * Rewrites all URL's in the CSS to absolute paths.
 * 
 * @author Anthony Short
 */
class Absolute_Urls
{
  /**
   * Takes a CSS string, rewrites all URL's using Scaffold's built-in find_file method
   *
   * @author Anthony Short
   * @param $css
   * @return $css string
   */
  public static function formatting_process()
  {
    // Get expires cache flush counter
    $counter = sprintf('%d', (int) $_GET['c']);

    // The absolute url to the directory of the current CSS file
    $dirPath = SCAFFOLD_DOCROOT . Scaffold::url_path(Scaffold::$css->path);
    $dir = rtrim(SCAFFOLD_URLPATH, '\\/') . '/' . str_replace(rtrim(SCAFFOLD_DOCROOT, '\\/') . DIRECTORY_SEPARATOR, '', Scaffold::$css->path);
    //$dir = str_replace('\\', '/', SCAFFOLD_URLPATH . str_replace(SCAFFOLD_DOCROOT, '', Scaffold::$css->path));

    // @imports - Thanks to the guys from Minify for the regex :)
    if( preg_match_all(
            '/
              @import\\s+
              (?:url\\(\\s*)?      # maybe url(
              [\'"]?               # maybe quote
              (.*?)                # 1 = URI
              [\'"]?               # maybe end quote
              (?:\\s*\\))?         # maybe )
              ([a-zA-Z,\\s]*)?     # 2 = media list
              ;                    # end token
          /x'
            , Scaffold::$css->string # Webligo - PHP5.1 compat
            , $found
        ) ) {
      foreach( $found[1] as $key => $value ) {
        # Should we skip it
        if( self::skip($value) ) continue;

        $media = ($found[2][$key] == "") ? '' : ' ' . preg_replace('/\s+/', '', $found[2][$key]);

        # Absolute path
        $absolute = self::up_directory($dir, substr_count($url, '..' . DIRECTORY_SEPARATOR, 0)) . str_replace('..' . DIRECTORY_SEPARATOR, '', $url);
        $absolute = str_replace('\\', '/', $absolute);

        # Rewrite it
        # Webligo - PHP5.1 compat
        Scaffold::$css->string = str_replace($found[0][$key], '@import \''
            . $absolute . '\''
            . $media . '?c=' . $counter . ';', Scaffold::$css->string);
      }
    }

    # Convert all url()'s to absolute paths if required
    if( preg_match_all('/url\\(\\s*([^\\)\\s]+)\\s*\\)/',
        Scaffold::$css->__toString(), $found) ) { // Webligo - PHP5.1 compat
      foreach( $found[1] as $key => $value ) {
        // START - Webligo Developments
        $original = $found[0][$key];
        $url = Scaffold_Utils::unquote($value);

        // Absolute Path (pseudo-skip)
        if( self::skip($url) ) {
          if( substr($url, 0, 5) == "data:" ) { // Definitely skip this
            continue;
          }
          $absolute = $url;
        }
        // home path
        else if( $url[0] == '~' && $url[1] == '/' ) {
          $absolute = str_replace('\\', '/', rtrim(SCAFFOLD_URLPATH, '/\\') . '/' . ltrim($url, '~/'));
          $absolutePath = rtrim(SCAFFOLD_DOCROOT, '/\\') . DIRECTORY_SEPARATOR . ltrim($url, '~/');
        }
        // relative path
        else {
          $absolute = str_replace('\\', '/', self::up_directory($dir, substr_count($url, '..' . DIRECTORY_SEPARATOR, 0)) . str_replace('..' . DIRECTORY_SEPARATOR, '', $url));
          $absolutePath = self::up_directory($dirPath, substr_count($url, '..' . DIRECTORY_SEPARATOR, 0)) . str_replace('..' . DIRECTORY_SEPARATOR, '', $url);
        }

        # If the file doesn't exist
        if( !Scaffold::find_file($absolutePath) ) {
            Scaffold::log("Missing image - {$absolute} / {$absolutePath}", 1);
        }

        // Add expires flush counter
        if( false === strpos($absolute, '?') ) {
          $absolute .= '?c=' . $counter;
        } else {
          $absolute .= '&c=' . $counter;
        }

        # Rewrite it
        Scaffold::$css->string = str_replace($original, 'url(' . $absolute . ')',
            Scaffold::$css->string); // Webligo - PHP5.1 compat
        // END - Webligo Developments
      }
    }
  }

  /**
   * Skip a path for rewriting
   *
   * @author Anthony Short
   * @param $url
   * @return boolean
   */
  private static function skip($url)
  {
    // Absolute Path
    if( $url[0] == DIRECTORY_SEPARATOR ||
        $url[0] == "\\" ||
        $url[0] == "/" ||
        substr($url, 0, 7) == "http://" ||
        substr($url, 0, 5) == "data:"
    ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Takes a path, and goes back x number of directories.
   *
   * @author Anthony Short
   * @param $path The path
   * @param $n The number of directories to go back
   * @return string
   */
  public static function up_directory($path, $n)
  {
    $exploded = explode(DIRECTORY_SEPARATOR, $path);
    $exploded = array_slice($exploded, 0, (count($exploded) - $n));
    return implode(DIRECTORY_SEPARATOR, $exploded) . DIRECTORY_SEPARATOR;
  }

}