<?php

# Avoids illegal processing, doesn't cost much, but unnecessary on a correct installation
if (!defined('MEDIAWIKI')) { die(-1); } 

if ( !defined( 'SMW_VERSION' ) ) {
	die( '<b>Error:</b> You need to have <a href="https://semantic-mediawiki.org/">Semantic MediaWiki</a> installed in order to use Semantic Data Import.' );
}
if ( version_compare( SMW_VERSION, '1.9', '<' ) ) {
	die( '<b>Error:</b> Semantic Data Import requires Semantic MediaWiki 1.9 or above.' );
}

call_user_func( function () {

	# Extension Declaration
	$GLOBALS['wgExtensionCredits']['semantic'][] = array(
			'path' => __FILE__,
			'name' => 'Semantic Data Import',
			'author' => array('Toni Hermoso'),
			'version' => '0.3.2',
			'url' => 'https://github.com/toniher/SemanticDataImport',
			'descriptionmsg' => 'sdimport-desc'
	);
	
	$GLOBALS['wgMessagesDirs']['SemanticDataImport'] = __DIR__ . '/i18n';
	$GLOBALS['wgExtensionMessagesFiles']['SemanticDataImport'] = __DIR__ . '/SemanticDataImport.i18n.php';
	$GLOBALS['wgExtensionMessagesFiles']['SemanticDataImportMagic'] = __DIR__ . '/SemanticDataImport.i18n.magic.php';
	$GLOBALS['wgAutoloadClasses']['SDImportData'] = __DIR__ . '/includes/SDImportData.php';
	$GLOBALS['wgAutoloadClasses']['SDImportDataParser'] = __DIR__ . '/includes/SDImportData.parser.php';
	$GLOBALS['wgAutoloadClasses']['SDImportApi'] = __DIR__ . '/includes/SDImportApi.php';

	$GLOBALS['wgAPIModules']['sdimport'] = 'SDImportApi';

	$GLOBALS['wgHooks']['ParserFirstCallInit'][] = 'SDImportParserFunction';
	$GLOBALS['wgHooks']['PageContentSaveComplete'][] = 'SDImportData::saveJSONData';

    $GLOBALS['wgAutoloadClasses']['SpecialSDImport'] = __DIR__ . '/includes/SDImport.special.php';
	$GLOBALS['wgAutoloadClasses']['SDImportReader'] = __DIR__ . '/includes/SDImportReader.php';

    # SpecialPage referencing
    $GLOBALS['wgSpecialPages']['SDImport'] = 'SpecialSDImport';

	$GLOBALS['wgResourceModules']['ext.sdimport'] = array(
		'localBasePath' => dirname( __FILE__ ),
		'scripts' => array( 'libs/handsontable/handsontable.full.js', 'libs/sdimport.js', 'libs/sdimport.special.js' , 'libs/jquery.csv.min.js'),
		'styles' => array( 'libs/handsontable/handsontable.full.css', 'libs/sdimport.less', 'libs/sdimport.special.css' ),
		'dependencies' => array(
			'mediawiki.jqueryMsg',
		),
		'messages' => array(
			'sdimport-commit',
			'sdimport-form-submit-success',
			'sdimport-form-back',
			'sdimport-form-edit-rowobject-label',
			'sdimport-form-edit-rowobject-button',
			'sdimport-form-empty-file',
			'sdimport-form-error',
			'sdimport-form-restrict-extensions'
		),
		'remoteExtPath' => 'SemanticDataImport'
	);
	
	$GLOBALS['wgResourceModules']['ext.sdimport.form'] = array(
		'localBasePath' => dirname( __FILE__ ),
		'scripts' => array( 'libs/survey-jquery/survey.jquery.js' ),
		'styles' => array( 'libs/survey-jquery/survey.css' ),
		'dependencies' => array(
			'mediawiki.jqueryMsg',
		),
		'remoteExtPath' => 'SemanticDataImport'
	);
	
	# Loading for domains which are JSON-based
	$GLOBALS['wgHooks']['OutputPageBeforeHTML'][] = 'SDImportData::onOutputPageBeforeHTML';
	
	# Export Vars into JS
	$GLOBALS['wgHooks']['ResourceLoaderGetConfigVars'][] = 'SDImportData::onResourceLoaderGetConfigVars';
	
	
	$GLOBALS['$wgSDImportDataPageFileLimitSize'] = '1073741824';
	
	# Example NS definition
	#$GLOBALS["wgSDImportDataPage"]["SDImport"] = array();
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["edit"] = false;
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["editfields"] = false;
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["separator"] = "\t";
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["delimiter"] = '"';
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["rowobject"] = "SDImport";
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["rowfields"] = array("Page1", "Page2");
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["typefields"] = array("Page", "Page");
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["ref"] = array("ref" => "{{PAGENAME}}");
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["prefields"] = array( "", "" );
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["postfields"] = array( "", "" );
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["json"] = false; # Whether content is stored directly in JSON
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["single"] = false; # Whether to store straight properties-values, but not Subobject (rowobject is ignored)
	#$GLOBALS["wgSDImportDataPage"]["SDImport"]["form"] = false; # Whether to use form instead of spreadsheet (only when single)
	#define("NS_SDImport", 2000);
	#$wgExtraNamespaces[NS_SDImport] = "SDImport";
	#$GLOBALS['smwgNamespacesWithSemanticLinks'][NS_SDImport] = true;

} );


// Hook our callback function into the parser
function SDImportParserFunction( $parser ) {

	$parser->setHook( 'smwdata', 'SDImportDataParser::processData' );
	$parser->setFunctionHook( 'smwdatalink', 'SDImportDataParser::prepareLink', SFH_OBJECT_ARGS );

	return true;
}
