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

// Lade den Pfad zum RSS-Feed aus den Moduleinstellungen
$rssUrl = $params->get('rssurl', '');

// Lade das RSS-Feed
$rss = simplexml_load_file($rssUrl);

// Überprüfen, ob das RSS-Feed erfolgreich geladen wurde
if ($rss) {
    // Entnahme des Titels, der Beschreibung und des Bildes des Feeds
    $Title = (string) $rss->channel->title;
    $Description = (string) $rss->channel->description;

    // Bild-URL des Shops aus dem RSS-Feed extrahieren (falls vorhanden)
    if (isset($rss->channel->image) && isset($rss->channel->image->url)) {
        $headImageUrl = (string) $rss->channel->image->url;
    }

    // Ausgabe des Feeds
    echo '<div class="rss-feed">';

    // Feed-Info
    echo '<div class="feed-info">';
    if ($headImageUrl && $params->get('schow_feed_image', '')) {
        echo '<img src="' . $headImageUrl . '" alt="' . $Title . '" class="fead-head-image">';
    }
    if ($params->get('show_feed_title', '')) {
        echo '<h1>' . $Title . '</h1>';
    }
    if ($params->get('schow_feed_description')) {
        echo '<p>' . $Description . '</p>';
    }
    echo '</div>';

    // Ausgabe der einzelnen Artikel
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
        if ($itemImageUrl && $params->get('show_item_image', '')) {
            echo '<div class="rss-item-image"><a href="' . $itemLink . '"><img src="' . $itemImageUrl . '" alt="' . $itemTitle . '"></a></div>';
        }
        echo '<div class="feed-item-description">';
        if (!empty($itemFeld1 && $params->get('show_item_date', ''))) {
            echo '<p>'. $itemFeld1 .'</p>';
        }
        if (!empty($itemFeld2 && $params->get('rssitemdate', ''))) {
            echo '<p>'. $itemFeld2 .'</p>';
        }
        if (!empty($itemFeld3 && $params->get('rssitemdate', ''))) {
            echo '<p>'. $itemFeld3 .'</p>';
        }
        if (!empty($itemFeld4 && $params->get('rssitemdate', ''))) {
            echo '<p>'. $itemFeld4 .'</p>';
        }
        echo '<p>' . strip_tags($itemDescription, '<div><p><a>') . '</p>';
        echo '<a href="' . $itemLink . '" class="button">Ab zum Angebot</a>'; // Button hinzufügen
        echo '</div></div>';
    }

    echo '</div>';
} else {
    echo 'Failed to load RSS feed.';
}
?>
