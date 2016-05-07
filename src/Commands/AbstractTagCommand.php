<?php

namespace Stats\Commands;

use Joomla\Controller\AbstractController;
use Stats\CommandInterface;
use Stats\GitHub\GitHub;

/**
 * Abstract command for processing tags from GitHub
 *
 * @method         \Stats\CliApplication  getApplication()  Get the application object.
 * @property-read  \Stats\CliApplication  $app              Application object
 *
 * @since          1.0
 */
abstract class AbstractTagCommand extends AbstractController implements CommandInterface
{
	/**
	 * GitHub API object.
	 *
	 * @var    GitHub
	 * @since  1.0
	 */
	protected $github;

	/**
	 * The GitHub repository to query.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $repoName;

	/**
	 * The owner of the GitHub repository to query.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $repoOwner;

	/**
	 * Constructor.
	 *
	 * @param   GitHub  $github  GitHub API object
	 *
	 * @since   1.0
	 */
	public function __construct(GitHub $github)
	{
		$this->github = $github;
	}

	/**
	 * Get the tags for a repository
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	protected function getTags()
	{
		$tags = [];

		$this->getApplication()->out('<info>Fetching page 1 of tags.</info>');

		// Get the first page so we can process the headers to figure out how many times we need to do this
		$tags = array_merge($tags, $this->github->repositories->getTags($this->repoOwner, $this->repoName, 1));

		$response = $this->github->repositories->getApiResponse();

		if (isset($response->headers['Link']))
		{
			preg_match('/(\?page=[0-9]+>; rel=\"last\")/', $response->headers['Link'], $matches);

			if ($matches && isset($matches[0]))
			{
				preg_match('/\d+/', $matches[0], $pages);

				$lastPage = $pages[0];

				for ($page = 2; $page <= $lastPage; $page++)
				{
					$this->getApplication()->out("<info>Fetching page $page of $lastPage pages of tags.</info>");

					$tags = array_merge($tags, $this->github->repositories->getTags($this->repoOwner, $this->repoName, $page));
				}
			}
		}

		return $tags;
	}
}
