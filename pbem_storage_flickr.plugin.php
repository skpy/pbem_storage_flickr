<?php
if ( !defined( 'HABARI_PATH' ) ) { die( 'No direct access' ); }

class pbem_storage_flickr extends Plugin
{

	public function filter_pbem_storage_provider( $providers )
	{
		$providers['flickr'] = 'Flickr';
		return $providers;
	}

	/**
	 * $media should be an array of filenames
	*/
	public function filter_pbem_store_flickr( $body, $media, $user, $tags )
	{
		$images = '';

		// instantiate the Flickr API
		$f = new Flickr ( array( 'user_id' => $user->id ) );
		// if we have only a single file, use the FlickrSilo's option for the size
		// otherwise, try to cram smaller images together
		switch( count( $media ) ) {
			case 1:
				$size = '';
				break;
			case 2:
				$size = 'm';
				break;
			default:
				$size = 't';
				break;
		}
		foreach( $media as $filename => $filepath ) {
			$id = $f->upload( $filepath, $filename, '', str_replace( ',', '', $tags), '', 0 );
			if ( ! $id || ! is_numeric( $id ) ) {
				EventLog::log( "Failed to upload to Flickr", 'warning', 'plugin', 'pbem' );
				continue;
			}
			EventLog::log("Uploaded photo $id to Flickr", 'info', 'plugin', 'pbem');
			unlink( $filepath );
			if ( '' != $images ) { $images .= ' '; }
			$images .= '[flickr id=' . $id;
			if ( $size ) {
				$images .= " size=$size";
			}
			if ( $user->info->pbem_class ) {
				$images .= ' class=' . $user->info->pbem_class;
			}
			$images .= ' /]';
		}
		return $images . "\n" . $body;
	}
}
?>
