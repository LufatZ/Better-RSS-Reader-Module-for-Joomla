<?php

/**
 * Better RSS Reader
 *
 * @package MOD_RSS_READER
 * @version 1.0.0
 * @author Lucas Damme lufatz@oxfatech.de
 * @license Proprietary Licence
 * @copyright (c) 2024 OxFaTech
 * @link https://oxfatech.de
 *
 * This module fetches and displays RSS feeds with customizable options.
 * The software is licensed under a proprietary license. Redistribution,
 * modification, or commercial use is prohibited without prior express
 * written permission from the copyright holder.
 * For details, see the LICENCE.txt file
 */

// Ensure the file is accessed from Joomla
defined('_JEXEC') or die();
echo '<div id="rss-reader-module" class="brssr rss rss-reader">';
// Store global parameters
$GLOBALS['params'] = $params;
// Debugging: Output parameters if debug mode is enabled
if (getConfig('debug')) {
    echo processText(text:print_r($params,true),tag:'div',class:'debug');
    // Start time measurement
    $startTime = microtime(true);
}
// Initialize Cache
$cache = JFactory::getCache('mod_rss_reader', 'output');
$cache->setCaching(getConfig('rssoutputcache'));
$cache->setLifeTime(900);// 15 Min
// Create a unique cache key based on module parameters
$cacheKey = md5(serialize($params));
// Try to load cached output
$output = $cache->get($cacheKey);

if ($output === false) {
    // Start output buffering
    ob_start();
    
    // Fetch the RSS URL from the module configuration
    $rssUrl = getConfig('rssurl');
    // suppress errors and process them manually
    libxml_use_internal_errors(true);
    // Attempt to load the RSS feed from the specified URL
    $rss = simplexml_load_file($rssUrl, 'SimpleXMLElement', LIBXML_NOCDATA);
    // Check if the RSS feed was successfully loaded
    if ($rss) {
        echo '<div class="rss rss-feed">';
        // Display the channel (feed) information if enabled
        if (getConfig('show_feed_channel')) {
            echo buildHead($rss);
        }
        // Display the individual feed items
        echo buildItems($rss);
        echo '</div>';
    } else {
        // Display an error message if the RSS feed could not be loaded
        echo '<p>Error: Failed to load RSS feed. Please check the feed URL.</p>';
        foreach (libxml_get_errors() as $error) {
            echo "<p>LibXML error: {$error->message}</p>";
        }
        libxml_clear_errors();
        return;
    }

    // Capture the output
    $output = ob_get_clean();
    // Store the output in cache
    $cache->store($output, $cacheKey);
}
if (getConfig('debug')) {
    // End time measurement
    $endTime = microtime(true);
    // Calculate execution time
    $executionTime = $endTime - $startTime;
    
    echo processText(text:'Execution time: ' . $executionTime . ' seconds',tag:'div',class:'debug');
}
echo $output;
echo '</div>';
JFactory::getDocument()->addStyleSheet(JURI::root() . '/modules/mod_rss_reader/css/default.css');
/**

* Fetch a configuration value from the global parameters

*

* @param string $config The name of the configuration parameter

* @return mixed The value of the configuration parameter, or an empty string if not set

*/
function getConfig($config) {
    return $GLOBALS['params']->get($config, '');
}

/**

* Process and return an image HTML element with optional linking and description

*

* @param string $url The URL of the image

* @param string $desc A description for the image (optional)

* @param string $link A URL to wrap the image with a hyperlink (optional)

* @param string $alt Alt text for the image (optional)

* @return string The processed image HTML element

*/
function processImg($url = '', $desc = '', $link = '', $alt = '') {
    $img = '';
    if ($url != '') {
        // Check if the image should be wrapped in a link and if a description should be included
        $isLinked = getConfig('link_image') && $link != '';
        $isDescribed = getConfig('show_image_desc') && $desc != '';
        // Build the <figure> element containing the image and its description (if applicable)
        $figure = '<figure>';
        if (getConfig('show_image')) {
            $figure .= '<img class="rss rss-img" src="' . $url . '" alt="'. $alt .'"/>';
        }
        if ($isDescribed) {
            $figure .= processText(tag: 'figcaption', text: $desc);
        }
        $figure .= '</figure>';
        // If linking is enabled, wrap the figure in a hyperlink
        if ($isLinked) {
            $img = processLink(link:$link, text:$figure, class:'rss rss-img-link');
        } else {
            $img = $figure;
        }
    }
    
    return $img;
}

/**

* Calculate the similarity between two texts as a percentage.

* This is used for detecting and avoiding duplicate or near-duplicate content.

*

* @param string $text1 The first text string

* @param string $text2 The second text string

* @return float The similarity percentage between the two texts

*/
function calculateSimilarity($text1, $text2) {
    // Remove HTML tags and split the text into words
    $words1 = preg_split('/\s+/', strip_tags($text1));
    $words2 = preg_split('/\s+/', strip_tags($text2));
    // Find common words between the two texts
    $commonWords = array_intersect($words1, $words2);
    // Calculate the total word count
    $totalWords = count($words1) + count($words2);
    
    // Calculate and return the similarity as a percentage
    return (2 * count($commonWords)) / $totalWords * 100;
}

/**

* Limit the number of words in a text string, appending an ellipsis if truncated.

*

* @param string $text The text to limit

* @param int $limit The maximum number of words allowed

* @return string The truncated text, with an ellipsis if necessary

*/
function limitWords($text = '', $limit = 0) {
    if ($limit <= 0) {
        return $text;
    }
    // Remove HTML tags and split the text into words
    $words = preg_split('/\s+/', strip_tags($text));
    
    // If the text exceeds the word limit, truncate and append an ellipsis
    if (count($words) > $limit) {
        $text = implode(' ', array_slice($words, 0, $limit)) . '...';
    }
    return $text;
}

/**

* Create an HTML hyperlink element

*

* @param string $link The URL for the hyperlink

* @param string $text The text to display for the hyperlink

* @param string $class CSS classes to apply to the hyperlink (optional)

* @return string The generated hyperlink HTML element

*/
function processLink($link = '#', $text = '', $class = '') {
    return $link ? '<a href="' . $link . '" class="' . $class . '">' . $text . '</a>' : $text;
}

/**

* Wrap text in an HTML tag with an optional CSS class

*

* @param string $tag The HTML tag to wrap the text in

* @param string $text The text to wrap

* @param string $class CSS classes to apply to the tag (optional)

* @return string The generated HTML element with the wrapped text

*/
function processText($tag = 'p', $text = '', $class = '') {
    return $text ? '<' . $tag . ' class="' . $class . '">' . $text . '</' . $tag . '>' : '';
}
/**

* Format a date string in 'd.m.Y H:i' format.

*

* @param string $date The date string to format

* @return string The formatted date

*/
function processDate($date) {
    return (new DateTime($date))->format('d.m.Y H:i');
}
/**

* Build and display the RSS feed channel header, including title, description, and image.

*

* @param SimpleXMLElement $rss The loaded RSS feed

*/
function buildHead($rss) {
    //initialize content variables
    $rssHead = '';
    $chImTitle = '';
    $chDescription = '';
    $chImage = '';
    $chLang = '';
    $chRights = '';
    $chContactTec = '';
    $chContactCon = '';
    $chPubDate = '';
    $chCategory = '';
    $chGenerator = '';
    
    // Prepare the channel title
    if (getConfig('show_feed_title') && isset($rss->channel->title)) {
        $chTitle = processText(text:$rss->channel->title,tag:getConfig('feed_title_tag'));
    }
    // Prepare the channel description
    if (getConfig('show_feed_description') && isset($rss->channel->description)) {
        $chDescription = '<div class="rss rss-description-container">' . processText(text: $rss->channel->description) . '</div>';
    }
    // Prepare the channel image
    if (getConfig('show_image') && isset($rss->channel->image)) {
        $chImUrl = $rss->channel->image->url;
        $chImage = processImg(
            $rss->channel->image->url,
            $rss->channel->image->description,
            $rss->channel->image->link,
            $rss->channel->image->title
            );
    }
    // Prepare other optional channel elements (lang, date, contacts, ...)
    if (getConfig('show_feed_language')&&isset($rss->channel->language)) {
        $chLang = processText(text: $rss->channel->language);
    }
    if (getConfig('show_feed_copyright')&&isset($rss->channel->copyright)) {
        $chRights = processText(text: $rss->channel->copyright);
    }
    //(technical contact)
    if (getConfig('show_feed_web_master')&&isset($rss->channel->webMaster)) {
        $chContactTec = processText(text:$rss->channel->webMaster);
    }
    //(content contact)
    if (getConfig('show_feed_managing_editor')&&isset($rss->channel->managingEditor)) {
        $chContactCon = processText(text: $rss->channel->managingEditor);
    }
    if (getConfig('show_feed_pub_date')&&isset($rss->channel->pubDate)) {
        $chPubDate = processText(text: processDate($rss->channel->pubDate));
    }
    if (getConfig('show_feed_category')&&isset($rss->channel->category)) {
        $chCategory = processText(text: $rss->channel->category);
    }
    //(e.g. OxFaTech Feed Generator v1.0)
    if (getConfig('show_feed_generator')&&isset($rss->channel->generator)) {
        $chGenerator = processText(text: $rss->channel->generator);
    }
    
    // Output the channel header
    $rssHead .= '<div class="rss rss-reader rss-channel rss-head" id="rss-head">';
    $rssHead .= $chImage;
    $rssHead .= $chTitle;
    $rssHead .= $chLang;
    $rssHead .= $chRights;
    $rssHead .= $chContactTec;
    $rssHead .= $chContactCon;
    $rssHead .= $chDescription;
    $rssHead .= $chPubDate;
    $rssHead .= $chCategory;
    $rssHead .= $chGenerator;
    $rssHead .= '</div>';
    
    return $rssHead;
}

/**

* Build and display the RSS feed items, including title, description, and other optional elements.

*

* @param SimpleXMLElement $rss The loaded RSS feed

*/
function buildItems($rss) {
    $rssItems = '';
    // Initialize item counter and limit
    $itemCounter=0;
    $itemTarget= getConfig('item_count');
    
    // Loop through each item in the RSS feed
    foreach ($rss->channel->item as $item) {
        // Initialize item elements
        $itemTitle = '';
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
        
        //prepare item link
        if (isset($item->link)){
            $itemLink = (string) $item->link;
        }
        // Prepare item title
        if (getConfig('show_item_title')&&isset($item->title)) {
            $itemTitle = processLink(link:$itemLink,text:processText(text:$item->title,tag:getConfig('item_title_tag')));
        }
        //prepare item description
        if (getConfig('show_item_description')&&isset($item->description)) {
            $itemDescription = limitWords(limit: intval(getConfig('item_desc_word_count')),text: $item->description);
        }
        //prepare item content:encoded
        if (getConfig('show_item_content_encoded')&&isset($item->children('content', true)->encoded)) {
            $itemEncoded = $item->children('content', true)->encoded;
        }
        //prepare item image
        if (getConfig('show_image')){
            //initialize img variables
            $itemImageUrl = '';
            $itemImageAlt = '';
            $itemImageDesc = '';
            
            if (isset($item->enclosure)) {
                $itemImageUrl = (string) $item->enclosure['url'];
            }
            //support for media tag
            elseif (isset($item->children('media', true)->content->attributes()->url)){
                $itemImageUrl = $item->children('media', true)->content->attributes()->url;
                $itemImageAlt = $item->children('media', true)->credit;
                $itemImageDesc = $item->children('media',true)->description;
            }
            
            $itemImage = processImg(url: $itemImageUrl, link: $itemLink, alt: $itemImageAlt, desc: $itemImageDesc);
        }
        //prepare item author
        if (getConfig('show_item_author')) {
            $itemAuthorMail = '';
            $itemAuthorName = '';
            
            if (isset($item->author)) {
                $itemAuthorMail = (string) $item->author;
            };
            //support dc tag
            if (isset($item->children('dc', true)->creator)){
                $itemAuthorName = $item->children('dc', true)->creator;
            }
            
            $itemAuthor = processText(text: $itemAuthorName . $itemAuthorMail, tag:'div');
        }
        //prepare item categories
        if (getConfig('show_item_category')&&isset($item->category)) {
            $itemCategories .= '<ul>';
            //add a new category entry in each loop
            foreach ($item->category as $category){
                $categoryUrl = '';
                $categoryName = $category;
                
                if (isset($category['domain'])){
                    $categoryUrl = $category['domain'];
                }
                $itemCategories .= processText(text:processLink(link:$categoryUrl,text:$categoryName),tag:'li');
            }
            $itemCategories .= '</ul>';
        }
        //prepare link to item comments
        if (getConfig('show_item_comments_link')&&isset($item->comments)) {
            $itemCommentsUrl = processLink(link:$item->comments, text:'comments');
        }
        //prepare item date
        if (getConfig('show_item_date')&&isset($item->pubDate)){
            $itemDate = processText(text: processDate($item->pubDate));
        }
        //prepare link to item source rss file
        if (getConfig('show_item_source')&&isset($item->source)) {
            $itemSource = processLink(link: $item->source, text:'source');
        }
        //prepare link to item
        if ($itemLink != ''){
            $readMore = processLink(link: $itemLink,text:'read more');
        }
        if ($FieldsConfig = getConfig('item_additional_fields')) {
            //TODO: Namespaces
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
                    case 8:
                        $additionalFieldsLinkText = $item->$tagName;
                        if ($field->additional_field_link_text != "") {
                            $additionalFieldsLinkText = $field->additional_field_link_text;
                        }
                        $additionalFields.= processLink(link:$item->$tagName,text:$additionalFieldsLinkText);
                        break;
                    default: break;
                }
            }
        }
        // Output the item elements
        $rssItems .= '<div class="rss rss-item" id="rss-item-' . $itemCounter . '">';
        $rssItems .= $itemTitle;
        $rssItems .= $itemDate;
        if (calculateSimilarity($itemEncoded, $itemImage.$itemDescription)<70) {
            $rssItems .= $itemEncoded;
            $rssItems .= $itemDescription;
            $rssItems .= $itemImage;
        }else {
            $rssItems .= $itemDescription;
            $rssItems .= $itemImage;
        }
        $rssItems .= $additionalFields;
        $rssItems .= $itemCategories;
        $rssItems .= '<div class="rss rss-item-footer">';
        $rssItems .= $itemAuthor;
        $rssItems .= $itemSource;
        $rssItems .= $readMore;
        $rssItems .= '</div>';
        $rssItems .= '</div>';
        
        // Increment the item counter
        $itemCounter++;
        // Stop if the item limit is reached
        if ($itemCounter == $itemTarget && getConfig('item_count') != 0) {
            break;
        }
    }
    return $rssItems;
}
?>
