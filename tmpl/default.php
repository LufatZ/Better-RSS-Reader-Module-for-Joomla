<?php

/**
 * Better RSS Reader
 * 
 * @license Proprietary Licence
 * @copyright (c) 2024 OxFaTech
 * 
 * This software is licensed under a proprietary license.
 * Any distribution, modification, or commercial use is prohibited without prior express written permission from the copyright holder.
 * For details, see the LICENSE.txt file.
 */

defined('_JEXEC') or die;

$GLOBALS['params'] = $params;

if (getConfig('debug')) {
    var_dump($params);                                                                  //DEBUG: display params
}

$rssUrl = getConfig('rssurl');                                                          //load rss url
$rss = simplexml_load_file($rssUrl);                                                    //load rss file from url

if ($rss) {                                                                             //check if rss is loaded successfully
    echo '<div class="rss rss-feed">';                                                  //open tag for rss html output
    if (getConfig('show_feed_channel')) {
        buildHead($rss);                                                                //function to build and echo channel head
    }                                                            
        buildItems($rss);                                                               //function to build and echo rss items
    echo '</div>';                                                                      //close tag for rss html output
} else {
    echo '<p>Failed to load RSS feed.</p>';
}

function getConfig($config) {                                                           //function to extract configs from $params
    return $GLOBALS['params']->get($config, '');
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
        $chTitle = '<h1>' . (string) $rss->channel->title . '</h1>';
    }
    
    if (getConfig('show_feed_description') && isset($rss->channel->description)) {      //get config and prepare channel description
        $chDescription = '<div class="rss rss-description-container"><p>' . (string) $rss->channel->description . '</p></div>'; 
    }
    
    if (getConfig('show_feed_image') && isset($rss->channel->image)) {                  //get cofig and prepare channel image                                   
        $chImUrl = $rss->channel->image->url;
        $chImTitle = $rss->channel->image->title;
        $chImLink = $rss->channel->image->link;
        $chImDescription = $rss->channel->image->description;
        
        $chImage = 
            '<div class="rss rss-image-container">
                <a href="'. $chImLink .'" class="rss rss-image-link">
                    <figure>
                        <img class="rss rss-image" src="'. $chImUrl .'" alt="'. $chImTitle .'" />
                        <figcaption>'. $chImDescription .'</figcaption>
                    </figure>
                </a>
            </div>';
    }
    
    if (getConfig('show_feed_language')&&isset($rss->channel->language)) {              //get cofig and prepare channel language
        $chLang = '<p>'.$rss->channel->language.'</p>';
    }
    
    if (getConfig('show_feed_copyright')&&isset($rss->channel->copyright)) {            //get cofig and prepare channel copyright
        $chRights = '<p>'.$rss->channel->copyright.'</p>';
    }
    
    if (getConfig('show_feed_web_master')&&isset($rss->channel->webMaster)) {           //get cofig and prepare channel web master (technical contact)
        $chContactTec = '<p>'.$rss->channel->webMaster.'</p>';
    }
    if (getConfig('show_feed_managing_editor')&&isset($rss->channel->managingEditor)) { //get cofig and prepare channel managing editor (content contact)
        $chContactCon = '<p>'.$rss->channel->managingEditor.'</p>';
    }
    if (getConfig('show_feed_pub_date')&&isset($rss->channel->pubDate)) {               //get cofig and prepare channel publishing date
        $chPubDate = '<p>'.$rss->channel->pubDate.'</p>';
    }
    if (getConfig('show_feed_category')&&isset($rss->channel->category)) {              //get cofig and prepare channel category
        $chCategory = '<p>'.$rss->channel->category.'</p>';
    }
    if (getConfig('show_feed_generator')&&isset($rss->channel->generator)) {            //get cofig and prepare feed generator (e.g. OxFaTech Feed Generator v1.0)
        $chGenerator = '<p>'.$rss->channel->generator.'</p>';
    }
        
    echo '<div class="rss rss-reader rss-channel rss-head" id="rss-head"';              //open head container
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
    // Ausgabe der einzelnen Artikel
    $itemCounter=0;
    $itemTarget= getConfig('item_count');
    foreach ($rss->channel->item as $item) {
        // Titel, Link und Beschreibung extrahieren
        $itemTitle = (string) $item->title;
        $itemLink = (string) $item->link;
        $itemDescription = (string) $item->description;
        $itemImageUrl = (string) $item->enclosure['url'];
        $itemFeld1 = (string) $item->feld1;
        $itemFeld2 = (string) $item->feld2;
        $itemFeld3 = (string) $item->feld3;
        $itemFeld4 = (string) $item->feld4;
        
        
        // Ausgabe des Items
        echo '<div class="rss-item">';
        echo '<h2><a href="'.$itemLink.'">' . $itemTitle . '</a></h2>';
        if ($itemImageUrl && getConfig('show_item_image', '')) {
            echo '<div class="rss-item-image"><a href="' . $itemLink . '"><img src="' . $itemImageUrl . '" alt="' . $itemTitle . '"></a></div>';
        }
        echo '<div class="feed-item-description">';
        if (!empty($itemFeld1 && getConfig('show_item_date'))) {
            echo '<p>'. $itemFeld1 .'</p>';
        }
        if (!empty($itemFeld2 && getConfig('rssitemdate'))) {
            echo '<p>'. $itemFeld2 .'</p>';
        }
        if (!empty($itemFeld3 && getConfig('rssitemdate'))) {
            echo '<p>'. $itemFeld3 .'</p>';
        }
        if (!empty($itemFeld4 && getConfig('rssitemdate'))) {
            echo '<p>'. $itemFeld4 .'</p>';
        }
        echo '<p>' . strip_tags($itemDescription, '<div><p><a>') . '</p>';
        echo '<a href="' . $itemLink . '" class="button">Ab zum Angebot</a>'; // Button hinzufügen
        echo '</div></div>';
        
        $itemCounter++;
        if ($itemCounter == $itemTarget && getConfig('item_count') != 0) {
            break;
        }
    }
}
?>
