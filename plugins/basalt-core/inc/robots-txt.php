<?php
/**
 * robots.txt, and what to tell the AI crawlers.
 *
 * WordPress serves a virtual robots.txt with two lines in it. This adds the
 * rules a normal site wants (search results are not worth crawling) and the
 * one decision that is genuinely new: whether the models get to read the site.
 *
 * That decision is not one answer. There are two kinds of crawler behind the
 * same label:
 *
 * - The ones that collect text to train on. They take and give nothing back.
 * - The ones that fetch a page because somebody asked a question, and then
 *   name the source. Those send visitors, and for a local business they are
 *   turning into what a search result used to be.
 *
 * So the setting has three positions rather than a checkbox, and the middle
 * one, which lets the citing crawlers in and keeps the training ones out, is
 * the one most small sites actually want.
 *
 * @package BasaltCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Crawlers that collect text for training and give nothing back.
 *
 * @return string[]
 */
function basalt_core_training_crawlers(): array {
	/**
	 * Filter the training crawler list.
	 *
	 * @param string[] $agents User agent tokens.
	 */
	return (array) apply_filters(
		'basalt_core_training_crawlers',
		array(
			'GPTBot',
			'ClaudeBot',
			'anthropic-ai',
			'CCBot',
			'Google-Extended',
			'Applebot-Extended',
			'meta-externalagent',
			'FacebookBot',
			'Bytespider',
			'Diffbot',
			'Omgilibot',
			'ImagesiftBot',
			'AI2Bot',
			'cohere-ai',
			'PanguBot',
			'Timpibot',
		)
	);
}

/**
 * Crawlers that fetch a page to answer a question and name the source.
 *
 * @return string[]
 */
function basalt_core_citation_crawlers(): array {
	/**
	 * Filter the citing crawler list.
	 *
	 * @param string[] $agents User agent tokens.
	 */
	return (array) apply_filters(
		'basalt_core_citation_crawlers',
		array(
			'OAI-SearchBot',
			'ChatGPT-User',
			'Claude-User',
			'Claude-SearchBot',
			'PerplexityBot',
			'Perplexity-User',
			'DuckAssistBot',
			'Applebot',
		)
	);
}

/**
 * Whether a physical robots.txt is shadowing the one WordPress builds.
 *
 * A file in the web root wins, and every setting on this screen is then
 * ignored without any error to explain why.
 *
 * @return bool
 */
function basalt_core_has_static_robots(): bool {
	return file_exists( ABSPATH . 'robots.txt' );
}

/**
 * Add the rules.
 *
 * @param string $output The robots.txt WordPress built.
 * @param bool   $public Whether the site is set to be indexed.
 * @return string
 */
function basalt_core_robots_txt( $output, $public ) {
	// A site set to discourage search engines already says Disallow: /.
	if ( ! $public ) {
		return $output;
	}

	$lines = array();

	if ( basalt_core_get( 'robots_block_search' ) ) {
		$lines[] = '';
		$lines[] = '# Search results and the internal query: no ranking value, and they eat the crawl budget.';
		$lines[] = 'User-agent: *';
		$lines[] = 'Disallow: /?s=';
		$lines[] = 'Disallow: /search/';
		$lines[] = 'Disallow: /*?*replytocom=';
	}

	$mode = (string) basalt_core_get( 'robots_ai' );

	if ( 'allow' !== $mode ) {
		$blocked = basalt_core_training_crawlers();

		if ( 'block' === $mode ) {
			$blocked = array_merge( $blocked, basalt_core_citation_crawlers() );
		}

		$lines[] = '';
		$lines[] = 'citation' === $mode
			? '# Crawlers that only collect training data. The ones that cite a source are welcome above.'
			: '# No AI crawlers, neither the ones that train nor the ones that answer questions.';

		foreach ( $blocked as $agent ) {
			$lines[] = 'User-agent: ' . $agent;
		}

		$lines[] = 'Disallow: /';
	}

	$extra = trim( (string) basalt_core_get( 'robots_extra' ) );

	if ( '' !== $extra ) {
		$lines[] = '';
		$lines[] = $extra;
	}

	if ( basalt_core_get( 'llms_enabled' ) ) {
		$lines[] = '';
		$lines[] = '# A short summary of this site for language models.';
		$lines[] = '# ' . home_url( '/llms.txt' );
	}

	if ( ! $lines ) {
		return $output;
	}

	return rtrim( $output ) . "\n" . implode( "\n", $lines ) . "\n";
}
add_filter( 'robots_txt', 'basalt_core_robots_txt', 10, 2 );
