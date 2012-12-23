<?php

namespace Picdump\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Picdump\DemoBundle\Form\ContactType;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DemoController extends Controller
{
	public $albums_path = '/var/www/clients/client1/web1/web/media/albums/';
	public $albums_url = '/media/albums/';

    /**
     * @Route("/", name="_demo")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

	/**
	 * @Route("/albums/", name="_demo_all_albums")
	 * @Template()
	 */
	public function albumsAction()
	{
		$albums = array();

		$year_dirs = scandir($this->albums_path);
		rsort($year_dirs);

		// Loop over years
		foreach ($year_dirs as $year)
		{
			if ($year == '.' || $year == '..')
				continue;
			if (is_dir($this->albums_path.'/'.$year))
			{
				$year_dir_handle = opendir($this->albums_path.'/'.$year);
				// Loop over albums
				while ($album = readdir($year_dir_handle))
				{
					if ($album == '.' || $album == '..')
						continue;

					if (file_exists($this->albums_path.$year.'/'.$album.'/'.'thumbnail.jpg'))
						$thumbnail = $this->albums_url.$year.'/'.$album.'/'.'thumbnail.jpg';
					// No thumbnail? Just take the first picture in the folder
					else
					{
						$images = scandir($this->albums_path.$year.'/'.$album);
						foreach ($images as $image)
						{
							if (strpos($image, 'JPG') || strpos($image, 'jpg'))
							{
								$thumbnail = $this->albums_url.$year.'/'.$album.'/'.$image;
								break;
							}
						}
					}

					$albums[] = array('url'=> $year.'/'.$album,
									  'thumbnail' => isset($thumbnail) ? $thumbnail : null,
									  'name' => $album,
									  'year' => $year);
				}
				closedir($year_dir_handle);
			}
		}

		$album_name = 'Albums';

		if ($albums)
			return array('albums' => $albums, 'album_name' => $album_name);
	}

	/**
	 * @Route("/albums/{album}", name="_demo_albums", requirements={"album" = ".+"})
	 * @Template()
	 */
	public function albumAction($album)
	{
		if (is_dir($this->albums_path.'/'.$album))
		{
			list($year, $album_name) = explode('/', $album);

			$handle = opendir($this->albums_path.'/'.$album);
			$images = array();
			while ($file = readdir($handle))
			{
				if ($file == '.' || $file == '..' || $file == 'thumbnail.jpg')
					continue;

				$images[] = $this->albums_url.$album.'/'.$file;
			}

			return array('images' => $images,
					'album_name' => $album_name,
					'album_background' => $this->albums_url.$album.'/'.'thumbnail.jpg');
		}
	}
}
