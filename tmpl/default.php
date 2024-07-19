<?php

/**
 * Better RSS Reader
 * 
 * @package MOD_RSS_READER
 * @author Lucas Damme lufatz@oxfatech.de
 * @license Proprietary Licence
 * @copyright (c) 2024 OxFaTech
 * @link https://oxfatech.de
 * 
 * This software is licensed under a proprietary license.
 * Any distribution, modification, or commercial use is prohibited without prior express written permission from the copyright holder.
 * For details, see the LICENSE.txt file.
 */

defined('_JEXEC') or die;

$GLOBALS['params'] = $params;
if (getConfig('debug')) {
    processText(text:var_dump($params, true),tag:'div');                                                             //DEBUG: display params
}
$rssUrl = getConfig('rssurl');                                                          //load rss url
$rss = simplexml_load_file($rssUrl, 'SimpleXMLElement', LIBXML_NOCDATA);                //load rss file from url with CDATA support
if ($rss) {                                                                             //check if rss is loaded successfully
    echo '<div class="rss rss-feed">';                                                  //open tag for rss html output
    if (getConfig('show_feed_channel')) {
        buildHead($rss);                                                                //function to build and echo channel head
    }                                                            
        buildItems($rss);                                                               //function to build and echo rss items
    echo '</div>';                                                                      //close tag for rss html output
} else {
    echo processText(text: 'Failed to load RSS feed');
}

function getConfig($config) {                                                           //function to extract configs from $params
    return $GLOBALS['params']->get($config, '');
}
function processImg($url = '', $desc = '', $link = '', $alt = '') {                     // function to process images uniformly
    $img = '';                                                                          // initialize img
    if ($url != '') {
        
        $isLinked = getConfig('link_image') && $link != '';                             // check if the img should be linked
        $isDescribed = getConfig('show_image_desc') && $desc != '';                     // check if the img should be described
        
        $figure = '<figure>';                                                           // open figure tag
        if (getConfig('show_image')) {
            $figure .= '<img class="rss rss-img" src="' . $url . '" alt="'. $alt .'"/>';// insert image
        }
        if ($isDescribed) {
            $figure .= processText(tag: 'figcaption', text: $desc);                     // insert image description
        }
        $figure .= '</figure>';                                                         // close figure tag
        
        if ($isLinked) {                                                                // wrap with link if needed
            $img = processLink(link:$link, text:$figure, class:'rss rss-img-link');
        } else {
            $img = $figure;
        }
    }
    
    return $img;
}
function calculateSimilarity($text1, $text2) {                                          //Function for comparing the HTML output (percentage)
    $text1 = strip_tags($text1);                                                        //remove html tags
    $text2 = strip_tags($text2);
    
    $words1 = preg_split('/\s+/', $text1);                                               
    $words2 = preg_split('/\s+/', $text2);
    
    $commonWords = array_intersect($words1, $words2);
    
    $totalWords = count($words1) + count($words2);
    
    $similarity = (2 * count($commonWords)) / $totalWords * 100;
    
    return $similarity;
}

function limitWords($text = '', $limit = 0) {
    if ($limit <= 0) {
        return $text;
    }
    
    $text = strip_tags($text);
    $words = preg_split('/\s+/', $text);
    
    if (count($words) <= $limit) {
        return $text;
    }
    
    $limitedText = implode(' ', array_slice($words, 0, $limit));
    return processText(text: $limitedText . '...');
}

function processLink($link = '#', $text = '', $class = '') {
    if ($link=='') {
        return $text;
    }
    return '<a href="' . $link . '" class="' . $class . '">' . $text . '</a>';
}

function processText($tag = 'p', $text = '', $class = '') {
    if ($text == '') {
        return '';
    }
    return '<' . $tag . ' class="' . $class . '">' . $text . '</' . $tag . '>';
}
function processDate($date) {
    return (new DateTime($date))->format('d.m.Y H:i');
}

function buildHead($rss) {
    $chImTitle = '';                                                                    //initialize variables
    $chDescription = '';
    $chImage = '';
    $chLang = '';
    $chRights = '';
    $chContactTec = '';
    $chContactCon = '';
    $chPubDate = '';
    $chCategory = '';
    $chGenerator = '';
       
    if (getConfig('show_feed_title') && isset($rss->channel->title)) {                  //get config and prepare channel title
        $chTitle = processText(text:$rss->channel->title,tag:getConfig('feed_title_tag'));
    }
    if (getConfig('show_feed_description') && isset($rss->channel->description)) {      //get config and prepare channel description
        $chDescription = '<div class="rss rss-description-container">' . processText(text: $rss->channel->description) . '</div>'; 
    }
    if (getConfig('show_image') && isset($rss->channel->image)) {                       //get cofig and prepare channel image                                   
        $chImUrl = $rss->channel->image->url;
        $chImTitle = $rss->channel->image->title;
        $chImLink = $rss->channel->image->link;
        $chImDescription = $rss->channel->image->description;
        
        $chImage = processImg(url: $chImUrl, desc: $chImDescription, link: $chImLink, alt: $chImTitle);
    }
    
    if (getConfig('show_feed_language')&&isset($rss->channel->language)) {              //get cofig and prepare channel language
        $chLang = processText(text: $rss->channel->language);
    }
    if (getConfig('show_feed_copyright')&&isset($rss->channel->copyright)) {            //get cofig and prepare channel copyright
        $chRights = processText(text: $rss->channel->copyright);
    }
    if (getConfig('show_feed_web_master')&&isset($rss->channel->webMaster)) {           //get cofig and prepare channel web master (technical contact)
        $chContactTec = processText(text:$rss->channel->webMaster);
    }
    if (getConfig('show_feed_managing_editor')&&isset($rss->channel->managingEditor)) { //get cofig and prepare channel managing editor (content contact)
        $chContactCon = processText(text: $rss->channel->managingEditor);
    }
    if (getConfig('show_feed_pub_date')&&isset($rss->channel->pubDate)) {               //get cofig and prepare channel publishing date
        $chPubDate = processText(text: processDate($rss->channel->pubDate));
    }
    if (getConfig('show_feed_category')&&isset($rss->channel->category)) {              //get cofig and prepare channel category
        $chCategory = processText(text: $rss->channel->category);
    }
    if (getConfig('show_feed_generator')&&isset($rss->channel->generator)) {            //get cofig and prepare feed generator (e.g. OxFaTech Feed Generator v1.0)
        $chGenerator = processText(text: $rss->channel->generator);
    }
        
    echo '<div class="rss rss-reader rss-channel rss-head" id="rss-head">';             //open head container
    echo $chImage;                                                                      //insert content
    echo $chTitle;
    echo $chLang;
    echo $chRights;
    echo $chContactTec;
    echo $chContactCon;
    echo $chDescription;
    echo $chPubDate;
    echo $chCategory;
    echo $chGenerator;
    echo '</div>';                                                                      //close head container
}
function buildItems($rss) {                                                             
    $itemCounter=0;                                                                     //initialize counter variable to limit items
    $itemTarget= getConfig('item_count');                                               //initialize max items variable
    
    foreach ($rss->channel->item as $item) {                                            //loop trough each item of the rss feed
        $itemTitle = '';                                                                //initialize content variables
        $itemLink = '';
        $itemDescription = '';
        $itemImage = '';
        $itemAuthor = '';
        $itemCategories = '';
        $itemCommentsUrl = '';
        $itemDate = '';
        $itemSource = '';
        $readMore = '';
        $itemEncoded = '';
        $additionalFields = '';
        
        if (isset($item->link)){                                                        //prepare item link
            $itemLink = (string) $item->link;
        }
        if (getConfig('show_item_title')&&isset($item->title)) {                        //prepare item title
            $itemTitle = processLink(link:$itemLink,text:processText(text:$item->title,tag:getConfig('item_title_tag')));
        }
        if (getConfig('show_item_description')&&isset($item->description)) {            //prepare item description
            $itemDescription = limitWords(limit: intval(getConfig('item_desc_word_count')),text: $item->description);
        }
        if (getConfig('show_item_content_encoded')&&isset($item->children('content', true)->encoded)) { //prepare item content:encoded
            $itemEncoded = $item->children('content', true)->encoded;
        }
        if (getConfig('show_image')){                                                   //prepare item image
            $itemImageUrl = '';                                                         //initialize variables
            $itemImageAlt = '';
            $itemImageDesc = '';
            
            if (isset($item->enclosure)) {
                $itemImageUrl = (string) $item->enclosure['url'];
            }elseif (isset($item->children('media', true)->content->attributes()->url)){//support for media tag 
                $itemImageUrl = $item->children('media', true)->content->attributes()->url;
                $itemImageAlt = $item->children('media', true)->credit;
                $itemImageDesc = $item->children('media',true)->description;
            }
            
            $itemImage = processImg(url: $itemImageUrl, link: $itemLink, alt: $itemImageAlt, desc: $itemImageDesc);
        }
        if (getConfig('show_item_author')) {                                            //prepare item author
            $itemAuthorMail = '';
            $itemAuthorName = '';
            
            if (isset($item->author)) {
                $itemAuthorMail = (string) $item->author;
            };
            if (isset($item->children('dc', true)->creator)){                           //support dc tag
                $itemAuthorName = $item->children('dc', true)->creator;
            }
            
            $itemAuthor = processText(text: $itemAuthorName . $itemAuthorMail, tag:'div');
        }
        if (getConfig('show_item_category')&&isset($item->category)) {                  //prepare item categories
            $itemCategories .= '<ul>';
            
            foreach ($item->category as $category){                                     //add a new category entry in each loop
                $categoryUrl = '';
                $categoryName = $category;
                
                if (isset($category['domain'])){
                    $categoryUrl = $category['domain'];
                }                                                                       
                $itemCategories .= processText(text:processLink(link:$categoryUrl,text:$categoryName),tag:'li');
            }
            $itemCategories .= '</ul>';
        }
        if (getConfig('show_item_comments_link')&&isset($item->comments)) {             //prepare link to item comments
            $itemCommentsUrl = processLink(link:$item->comments, text:'comments');
        }
        if (getConfig('show_item_date')&&isset($item->pubDate)){                        //prepare item date
            $itemDate = processText(text: processDate($item->pubDate));
        }
        if (getConfig('show_item_source')&&isset($item->source)) {                      //prepare link to item source rss file
            $itemSource = processLink(link: $item->source, text:'source');
        }
        if ($itemLink != ''){                                                           //prepare link to item
            $readMore = processLink(link: $itemLink,text:'read more');
        }
        if ($FieldsConfig = getConfig('item_additional_fields')) {//TODO: Namespaces
            foreach ($FieldsConfig as $field) {
                $tagName = $field->additional_field_tag_name;
                $tagOption = $field->additional_field_tag_option;
                
                switch ($tagOption){
                    case 0: 
                        $additionalFields.= processText(text:$item->$tagName,tag:'h1');
                        break;
                    case 1:
                        $additionalFields.= processText(text:$item->$tagName,tag:'h2');
                        break;
                    case 2:
                        $additionalFields.= processText(text:$item->$tagName,tag:'h3');
                        break;
                    case 3:
                        $additionalFields.= processText(text:$item->$tagName,tag:'h4');
                        break;
                    case 4:
                        $additionalFields.= processText(text:$item->$tagName,tag:'h5');
                        break;
                    case 5:
                        $additionalFields.= processText(text:$item->$tagName,tag:'h6');
                        break;
                    case 6:
                        $additionalFields.= processText(text: $item->$tagName);
                        break;
                    case 7:
                        $additionalFieldsImg = '';
                        if ($item->$tagName != '') {
                            $additionalFieldsImg = $item->$tagName;
                        }
                        elseif ($item->$tagName['url'] != '') {
                            $additionalFieldsImg = $item->$tagName['url'];
                        }
                        $additionalFields.= processImg(url:$additionalFieldsImg);
                        break;
                    case 8: //TODO: Custom link text
                        $additionalFields.= processLink(link:$item->$tagName,text:$item->$tagName);
                        break;
                    default: break;
                }
            }
        }
        
        
        //TODO implement additional fields
        echo '<div class="rss rss-item" id="rss-item-' . $itemCounter . '">';
        echo $itemTitle;
        echo $itemDate;
        if (calculateSimilarity($itemEncoded, $itemImage.$itemDescription)<70) {
            echo $itemEncoded;
            echo $itemDescription;
            echo $itemImage;
        }else {
            echo $itemDescription;
            echo $itemImage;
        }
        echo $additionalFields;
        echo $itemCategories;
        echo '<div class="rss rss-item-footer">';
        echo $itemAuthor;
        echo $itemSource;
        echo $readMore;
        echo '</div>';
        echo '</div>';
        
        $itemCounter++;
        if ($itemCounter == $itemTarget && getConfig('item_count') != 0) {
            break;
        }
    }
}
?>
