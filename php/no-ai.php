<?php
/**
 * Block AI
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

global $wimbblock_ai_agents;
// https://github.com/ai-robots-txt/ai.robots.txt/blob/main/.htaccess 261006
$wimbblock_ai_agents =
'%AddSearchBot|AgentDataBot|AgentTimes|AI2Bot|AI2Bot-DeepResearchEval|Ai2Bot-Dolma|aiHitBot|AIWebIndex|amazon-kendra|amazon-QBusiness|Amazonbot|AmazonBuyForMe|Amzn-SearchBot|Amzn-User|Andibot|Anomura|anthropic-ai|ApifyBot|ApifyWebsiteContentCrawler|Applebot|Applebot-Extended|Aranet-SearchBot|atlassian-bot|Awario|AzureAI-SearchBot|bedrockbot|bigsur.ai|BixelBot|Bravebot|Brightbot|Brightbot 1.0|BuddyBot|Bytespider|CCBot|Channel3Bot|ChatGLM-Spider|ChatGPT Agent|ChatGPT-User|Claude-Code|Claude-SearchBot|Claude-User|Claude-Web|ClaudeBot|Cloudflare-AutoRAG|CloudflareBrowserRenderingCrawler|CloudVertexBot|Code|cohere-ai|cohere-training-data-crawler|Cotoyogi|CragCrawler|Crawl4AI|Crawlspace|Cursor|Datenbank Crawler|DeepSeekBot|Devin|Diffbot|Diffbot-User|DoubaoBot|DuckAssistBot|Echobot Bot|EchoboxBot|ERNIEBot|ExaBot|ExaSearchBot|FacebookBot|facebookexternalhit|Factset_spyderbot|FirecrawlAgent|FriendlyCrawler|GeistHaus-PageFetcher|Gemini-Deep-Research|Google-Agent|Google-CloudVertexBot|Google-Extended|Google-Firebase|Google-Gemini-CLI|Google-NotebookLM|GoogleAgent-Mariner|GoogleAgent-URLContext|GoogleOther|GoogleOther-Image|GoogleOther-Video|GPTBot|GrokAppAndroid|GrokBot|HenkBot|Hunyuan|iAskBot|iaskspider|iaskspider/2.0|ICC-Crawler|ImagesiftBot|imageSpider|img2dataset|ISSCyberRiskCrawler|kagi-fetcher|Kangaroo Bot|KeenableBot|Kimi-Agent|Kimi-SearchBot|Kimi-User|KimiBot|KlaviyoAIBot|KunatoCrawler|laion-huggingface-processor|LAIONDownloader|LCC|Lightpanda|LinerBot|Linguee Bot|LinkupBot|Manus-User|meta-externalagent|Meta-ExternalAgent|meta-externalfetcher|Meta-ExternalFetcher|meta-webindexer|MistralAI-Index|MistralAI-Training|MistralAI-User|MistralAI-User/1.0|MoonshotBot|Mozilla-Tabstack|MyCentralAIScraperBot|NagetBot|nbot|netEstate Imprint Crawler|newsai|NotebookLM|NovaAct|OAI-AdsBot|OAI-SearchBot|omgili|omgilibot|OpenAI|opencode|Operator|PanguBot|Panscient|panscient.com|Perplexity-User|PerplexityBot|PetalBot|PhindBot|Poggio-Citations|Poseidon Research Crawler|qodercli|QualifiedBot|Querit-SearchBot|QueritBot|QuillBot|quillbot.com|QwenBot|Reflectionbot|SBIntuitionsBot|Scrapy|SemrushBot-OCOB|SemrushBot-SWA|Shap-User|ShapBot|Sidetrade indexer bot|Spider|TavilyBot|Terra Cotta|TerraCotta|Thinkbot|TikTokSpider|Timpibot|TongyiBot|Trae|TwinAgent|UseAI|VelenPublicWebCrawler|WARDBot|Webzio-Extended|webzio-extended|wpbot|WRTNBot|xAI-Grok|YaK|YandexAdditional|YandexAdditionalBot|YiBot|YiyanBot|YouBot|ZanistaBot%i';

function wimbblock_no_ai( $table_name, $agent, $blocked, $id, $robots ) {
	global $wimbblock_ai_agents;
	global $wimbblock_test_to_block;
	if ( preg_match( $wimbblock_ai_agents, $agent, $match ) === 1 ) {
		if ( $robots === 'ai_check' ) {
			return ' * AI: ' . $match[0];
		} elseif ( $robots === 'testing' ) {
			$why                     = 'AI';
			$wimbblock_test_to_block = $why;
		} elseif ( $robots === false ) {
			wimbblock_counter( $table_name, 'block', $id );
			wimbblock_error_log( 'AI blocked: ' . $match[0] . ' * ' . $agent );
			status_header( 403 );
			echo 'AI is undesirable.';
			exit();
		} else {
			if ( $blocked === '0' ) {
				wimbblock_counter( $table_name, 'block', $id );
			}
			wimbblock_counter( $table_name, 'robots', $id );
			wimbblock_error_log( 'AI robots forbidden: ' . $match[0] . ' * ' . $agent );
			header( 'Content-Type: text/plain; charset=UTF-8' );
			echo "User-agent: *\r\n" .
			'Disallow: /' . "\r\n";
			exit;
		}
	}
}
