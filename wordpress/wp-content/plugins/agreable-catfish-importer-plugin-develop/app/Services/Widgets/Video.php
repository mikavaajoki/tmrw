<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use \stdClass;

/**
 * Class Video
 *
 * @package AgreableCatfishImporterPlugin\Services\Widgets
 */
class Video {
	/**
	 * @param $widgetDom
	 *
	 * @return stdClass
	 */
	public static function getFromWidgetDom($widgetDom) {
    $widgetData = new stdClass();
    $widgetData->type = 'embed';
    $videoIframe = $widgetDom->find('iframe');
    $facebookVideo = $widgetDom->find('.fb-video');

    if (!isset($videoIframe[0])) {
      $widgetData->embed = self::getFacebookEmbedUrl($facebookVideo[0]->{'data-href'});
    } else {
      $widgetData->embed = self::getYouTubeEmbedUrl($videoIframe[0]->src);
    }

    return $widgetData;
  }

  public static function getVideoFromHeader($provider, $videoId) {
    $widgetData        = new stdClass();
    $widgetData->type  = 'embed';

    switch ( $provider ) {
      case 'youtube':
        $widgetData->embed = "https://www.youtube.com/watch?v=".$videoId;
        break;
      // case 'jwplayer':
        // $widgetData->embed = "https://www.jwplayer.com/".$videoId;
        // break;
      case 'vimeo':
        $widgetData->embed = "https://vimeo.com/".$videoId;
        break;
      case 'facebook':
        $widgetData->embed = $videoId;
        break;
    }

    $widgetData->acf_fc_layout = 'embed';
    return $widgetData;
  }

	/**
	 * @param $url
	 *
	 * @return string
	 */
	protected static function getFacebookEmbedUrl($url) {
    if (strpos($url, 'https://www.facebook.com') === false) {
      if ($url[0] !== '/') {
        $url = '/' . $url;
      }
      $url = 'https://www.facebook.com' . $url;
    }
    return $url;
  }

	/**
	 * @param $url
	 *
	 * @return mixed|string
	 */
	public static function getYouTubeEmbedUrl($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
      $url = str_replace("//","", $url);
      $url = "http://" . $url;
    }
    if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $id)) {
      $values = $id[1];
    } else if (preg_match('/youtube\.com\/embed\/([^\&\?\/]+)/', $url, $id)) {
      $values = $id[1];
    } else if (preg_match('/youtube\.com\/v\/([^\&\?\/]+)/', $url, $id)) {
      $values = $id[1];
    } else if (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $id)) {
      $values = $id[1];
    }
    else if (preg_match('/youtube\.com\/verify_age\?next_url=\/watch%3Fv%3D([^\&\?\/]+)/', $url, $id)) {
        $values = $id[1];
    } else {
      return $url;
    }
    return "https://www.youtube.com/watch?v=".$values;
  }
}
