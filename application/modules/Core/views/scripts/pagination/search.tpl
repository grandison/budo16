<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: search.tpl 7904 2010-12-03 03:36:14Z john $
 * @author     John
 */
?>

<?php
  // Parse query and remove page
  if( !empty($this->query) && ( is_string($this->query) || is_array($this->query)) ) {
    $query = $this->query;
    if( is_string($query) ) $query = parse_str(trim($query, '?'));
    unset($query['page']);
    $query = http_build_query($query);
    if( $query ) $query = '?' . $query;
  } else {
    $query = '';
  }
  // Add params
  $params = ( !empty($this->params) && is_array($this->params) ? $this->params : array() );
  unset($params['page']);
?>


<?php if( $this->pageCount > 1 ): ?>
  <div class="pages">
    <ul class="paginationControl">
      <?php if( isset($this->previous) ): ?>
        <li>
          <?php echo $this->htmlLink(array_merge($params, array(
            'reset' => false,
            'page' => ( $this->pageAsQuery ? null : $this->previous ),
            'QUERY' => $query . ( $this->pageAsQuery ? '&page=' . $this->previous : '' ),
          )), $this->translate('&#171; Previous')) ?>
        </li>
      <?php endif; ?>
      <?php foreach ($this->pagesInRange as $page): ?>
        <?php if ($page != $this->current): ?>
          <li>
            <?php echo $this->htmlLink(array_merge($params, array(
              'reset' => false,
              'page' => ( $this->pageAsQuery ? null : $page ),
              'QUERY' => $query . ( $this->pageAsQuery ? '&page=' . $page : '' ),
            )), $page) ?>
          </li>
        <?php else: ?>
          <li class="selected">
            <a href='<?php echo $this->escape($this->url()) ?>'><?php echo $page; ?></a>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php if (isset($this->next)): ?>
        <li>
          <?php echo $this->htmlLink(array_merge($params, array(
            'reset' => false,
            'page' => ( $this->pageAsQuery ? null : $this->next ),
            'QUERY' => $query . ( $this->pageAsQuery ? '&page=' . $this->next : '' ),
          )), $this->translate('Next &#187;')) ?>
        </li>
      <?php endif; ?>
    </ul>
  </div>
<?php endif; ?>

