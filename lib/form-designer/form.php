<?php
/**
 * @package CTCT\Form Designer
 */

global $debug;

$debug = !empty($_REQUEST['debug']);

if(!$debug) {

	if( !defined( 'DOING_AJAX' ) ) {
		define('DOING_AJAX', true);
	}

	header_remove();
	@header('Content-Type: application/json;');

	if(function_exists('xdebug_disable')) { xdebug_disable(); }

	if(function_exists('error_reporting') && is_callable('error_reporting')) { error_reporting(0); }
} else {
	if(function_exists('error_reporting') && is_callable('error_reporting')) { error_reporting(E_ALL); }
	ini_set('display_errors', 1);
}

	function findFont($id = '') {
			switch($id) {

				case 'times':
					return "'Times New Roman', Times, Georgia, serif";
					break;
				case 'georgia':
					return "Georgia,'Times New Roman', Times, serif";
					break;
				case 'palatino':
					return "'Palatino Linotype', Palatino, 'Book Antiqua',Garamond, Bookman, 'Times New Roman', Times, Georgia, serif";
					break;
				case 'garamond':
					return "Garamond,'Palatino Linotype', Palatino, Bookman, 'Book Antiqua', 'Times New Roman', Times, Georgia, serif";
					break;
				case 'bookman':
					return "Bookman,'Palatino Linotype', Palatino, Garamond, 'Book Antiqua','Times New Roman', Times, Georgia, serif";
					break;
				case 'helvetica':
					return "'Helvetica Neue',HelveticaNeue, Helvetica, Arial, Geneva, sans-serif";
					break;
				case 'arial':
					return "Arial, Helvetica, sans-serif";
					break;
				case 'lucida':
					return "'Lucida Grande', 'LucidaGrande', 'Lucida Sans Unicode', Lucida, Verdana, sans-serif";
					break;
				case 'verdana':
					return "Verdana, 'Lucida Grande', Lucida, TrebuchetMS, 'Trebuchet MS',Geneva, Helvetica, Arial, sans-serif";
					break;
				case 'trebuchet':
					return "'Trebuchet MS', Trebuchet, Verdana, sans-serif";
					break;
				case 'tahoma':
					return "Tahoma, Verdana, Arial, sans-serif";
					break;
				case 'franklin':
					return "'Franklin Gothic Medium','FranklinGotITC','Arial Narrow Bold',Arial,sans-serif";
					break;
				case 'impact':
					return "Impact, Chicago, 'Arial Black', sans-serif";
					break;
				case 'arialblack':
					return "'Arial Black',Impact, Arial, sans-serif";
					break;
				case 'gillsans':
					return "'Gill Sans','Gill Sans MT', 'Trebuchet MS', Trebuchet, Verdana, sans-serif";
					break;
				case 'courier':
					return "'Courier New', Courier, Monaco, monospace";
					break;
				case 'lucidaconsole':
					return "'Lucida Console', Monaco, 'Courier New', Courier, monospace";
					break;
				case 'comicsans':
					return "'Comic Sans MS','Comic Sans', Sand, 'Trebuchet MS', cursive";
					break;
				case 'papyrus':
					return "Papyrus,'Palatino Linotype', Palatino, Bookman, fantasy";
					break;
			}
		}

	function makeFormField($field, $cc_request = array(), $currentform = false) {
		$asterisk = $bold = $italic = $required = $val = $size = '';
		$fields = '';
		$data = getData();
		if(!isset($field['id'])) { return ''; }

		if(is_array($field)) { extract($field); }
		ctctlog('makeFormField');
		r($field);
		$placeholder = '';

		if(isset($cc_request['fields'][$field['id']]['value']) && $currentform) {
			$val = htmlspecialchars(stripslashes(stripslashes($cc_request['fields'][$field['id']]['value'])));
		} else {
			if(!empty($val)) {
				$placeholder = ' placeholder=\''.htmlentities(stripslashes(stripslashes($val))).'\'';
			}
			if(!($t == 'b' || $t == 's' || $t == 'ta')) {
				$val = '';
			}
		}


		if(isset($label) && is_string($label)) { $label = htmlentities(stripslashes(stripslashes($label))); }

		// If this is the submit button, we add list selection
		$fields .= "<div class='cc_$id kws_input_container gfield'>";

		$class = '';
		if(!empty($bold)) { $class .= ' kws_bold'; }
		if(!empty($italic)) { $class .= ' kws_italic'; }
		$name = "fields[$id]";
		$requiredFields = '';

		// The field is required
		if(!empty($required)) {
			$required = " required";
			$reqlabel = isset($data['text']['reqlabel']) ? htmlentities($data['text']['reqlabel']) : 'The %s field is required';
			$asterisk = isset($data['reqast']) ? '<span class="req gfield_required" title="'.sprintf($reqlabel, $label).'">*</span>' : '';

			// If it's not a button or the Form Text field, we add a hidden input that saved the field as required.
			if(!($t == 'ta' || $t == 'b' || $t == 's')) {
				$requiredFields = "\n".'<input type="hidden" name="'.$name.'[req]" value="1" />';
			}
		}

		// It's a textarea (which is the HTML "Form Text" field)
		if($t === 'ta') {
			if(isset($data['output']) && $data['output'] == 'html') {
				$fields .= strip_tags(html_entity_decode(stripslashes(stripslashes($val))), '<b><strong><em><i><span><u><ul><li><ol><div><attr><cite><a><style><blockquote><q><p><form><br><meta><option><textarea><input><select><pre><code><s><del><small><table><tbody><tr><th><td><tfoot><thead><u><dl><dd><dt><col><colgroup><fieldset><address><button><aside><article><legend><label><source><kbd><tbody><hr><noscript><link><h1><h2><h3><h4><h5><h6><img>');
			} else {
				$fields .=  stripslashes(stripslashes($val));
			}
		}
		// It's a button (submit)
		else if($t === 'b' || $t === 's') {
			if(!empty($label)) {
				$fields .= "\n<label for='cc_{$form_id}{$id}' class='$class'>$label</label>\n";
			} else {
				$fields .= "\n<label for='cc_{$form_id}{$id}' class='$class'>\n";
			}

			$fields .= "\n<input type='submit' value='$val' class='$t button' id='cc_{$form_id}{$id}' name='constant-contact-signup-submit' >\n<div class='kws_clear'></div>";
			if(empty($label)) { $fields .= "\n</label>"; }
		}
		// Lists selection
		else if($t === 'lists') {
			if(!empty($label)) {
				$fields .= "\n<label class='$class'>$label</label>\n";
			}
			$fields .= '<!-- %%LISTSELECTION%% -->';
		}
		// It's a text field
		else {
			if(!empty($label)) { $fields .= "<label for='cc_{$form_id}{$id}' class='$class  gfield_label'>\n$label{$asterisk}</label>"; }
			if(!empty($size) && $t == 't') { $size = " size=\"$size\"";}
			$fields .= "<input type='text' value='$val'$size $placeholder name='".$name."[value]' class='{$t} $class{$required}' id='cc_{$form_id}{$id}' />\n";
		}
		if(!empty($label)) { $fields .= "\n".'<input type="hidden" name="'.$name.'[label]" value="'.htmlentities($label).'" />'; }
		$fields .= $requiredFields;
		$fields .= "\n</div>";
		return $fields;
	}

	function processForm() {
		$f = $uid = $required = $t = $label = $givethanks = $safesubscribe = $size = $name = $id = $fields = $labelsusesamecolor = $labelsusesamealign = $labelsusesamefont = $labelsusesamepadding = $bgrepeat = $lfont = $tfont = $widthtype = $backgroundtype = $blockalign = $intro = $size = $uniqueformid = $inputfields = '';
		$cc_request = array();
		ctctlog('processForm');
		$data = getData();

		if(isset($data['f'])) {
			if(isset($data['text'])) {
				$data['text'] = json_decode(stripslashes($data['text']), true);
			}
		}

		extract($data);

		// If there is more than one form...no repeating IDs
		$form_id_attr = 'constant-contact-signup';
		if(isset($cc_signup_count) && $cc_signup_count > 1) {
			$form_id_attr = 'constant-contact-signup-'.$cc_signup_count;
			r('signupcount: '.$cc_signup_count.'; $form_id_attr = '.$form_id_attr);
		} else {
			$cc_signup_count = 0;
		}

		// If we're fetching a specific form (sending a unique form ID with the request),
		// $currentform checks whether the requested form is the form that we're working with.
		$currentform = (isset($cc_request['uniqueformid']) && $cc_request['uniqueformid'] == $uniqueformid);


		if(isset($form)) { $selector = ' id="cc_form_'.$form.'"'; } else { $selector = ''; }

		// Only process one to speed up things.
		if(!empty($changed)) {
			ctctlog('Changed is set: '.$changed);

			foreach($f as $field) {
				$field['size'] = $size;
				$field['form_id'] = isset($cc_signup_count) ? $cc_signup_count : 1;
				if( isset( $field['id'] ) && $field['id'] == str_replace('_default', '', $changed) || $field['id'] == str_replace('_label', '', $changed)) {

					return makeFormField($field, $cc_request);
				};
			};
		} else {
			ctctlog('Changed is not set.');
		}

		ctctlog('fields:');
		r($f);

		if(is_array($f)) {

			$position = array();
			foreach ($f as $key => $field) {
				if(!isset($field['pos'])) {
					unset($f[$key]);
					continue;
				}
			    $position[$key] = $field['pos'];
			}
			array_multisort($position, SORT_ASC, $f);

			$lists_shown = false;
			foreach($f as $field) {
                $field['size'] = $size;
                $field['form_id'] = $cc_signup_count;
                if($field['t'] === 'lists') {
                	$lists_shown = true;
                }
				$fieldoutput = makeFormField($field, $cc_request, $currentform);
				$inputfields .= $fieldoutput;
            }

            // Make sure the lists fields are shown if not already added.
            if(!$lists_shown) {
            	$inputfields .= '<!-- %%LISTSELECTION%% -->';
            }

            // We need a submit field for some strange reason in some IE versions
            // Can't use a hidden field, either.
            $inputfields .= "\n<input type='submit' style='position:absolute; width:0;height:0;left:-9999px;' name='constant-contact-signup-submit' />";

            $inputfields = "\n".'<div class="kws_input_fields gform_fields">'.$inputfields."\n".'</div>';
        }

        #r(htmlentities($inputfields));

		if($safesubscribe != 'no') {
			$safesubscribelink = '<a href="http://katz.si/safesubscribe" target="_blank" class="cc_safesubscribe safesubscribe_'.$safesubscribe.'" rel="nofollow">Privacy by SafeSubscribe</a>';
		} else {
			$safesubscribelink = '';
		}
		$haserror = $errors = $success = $hidden = $action = '';
		if( !isset($data['output']) || isset($data['output']) && $data['output'] == 'html') {
			$haserror = '<!-- %%HASERROR%% -->';
			$action = '<!-- %%ACTION%% -->';
			$errors = '<!-- %%ERRORS%% -->';
			$success = '<!-- %%SUCCESS%% -->';
			$hidden = '<!-- %%HIDDEN%% -->';
		}
		if(empty($cc_success)) {
			$formInner = $errors . $success . $inputfields . $safesubscribelink . $hidden;
		} else {
			$formInner = $success . $hidden;
		}

		$form = <<<EOD
	<div class="kws_form gform_wrapper$haserror"$selector>
		<form id="$form_id_attr" action="$action" method="post">
			$formInner
			<div class="kws_clear"></div>
			<!-- Form Generated by the Constant Contact API WordPress Plugin by Katz Web Services, Inc. -->
		</form>
	</div>
EOD;
		if( empty( $debug) ) {
			$form = str_replace(array("\n", "\r", "\t"), ' ', $form);
			$form = preg_replace('/\s\s/ism', ' ', $form);
		}
		return $form;
	}

	function processStyle() {
		$required = $color2 = $tcolor = $lcolor = $bordercolor = $color6 = $color5 = $t = $label = $size = $name = $id = $fields = $labelsusesamecolor = $labelsusesamealign = $labelsusesamefont = $labelsusesamepadding = $givethanks = $safesubscribe = $blockalign = $bgcss = $gradheight = $lpad = $lalign = $bgimage = $bgpos = $bgrepeat = $lfont = $tfont = $f = $lsize = $talign = $width = $widthtype = $borderradius = $borderwidth = $paddingwidth = $formalign = $talign = $backgroundtype = $widthtype = $borderstyle = $tsize = $lsize = '';

		ctctlog('start processStyle');

		$data = getData();

		extract($data);

		if(isset($form)) { $selector = '#content #cc_form_'.$form; } else { $selector = 'html body div.kws_form'; }

		$bgtop = $color6;
		$bgtopraw = str_replace('#', '', $bgtop);
		$bgbottom = $color2;
		$bgbottomraw = str_replace('#', '', $bgbottom);
		$tfont = findFont($tfont);
		$lfont = findFont($lfont);
		if($widthtype == 'per') { $widthtype = '%'; }

		switch($backgroundtype) {
			case 'gradient':
				if($gradtype == 'horizontal') {
					$bgrepeat = 'left top repeat-y';
					$dimensions = "width=$gradheight&height=1";
					$bgback = $bgtop;
				} else {
					$dimensions = "height=$gradheight&width=1";
					$bgrepeat = 'left top repeat-x';
					$bgback = $bgbottom;
				}
				$bgcss = "background: $bgbottom url('{$path}ozhgradient.php?start=$bgtopraw&end=$bgbottomraw&type=$gradtype&$dimensions') $bgrepeat;";
				break;
			case 'solid':
				$bgcss = "background-color: $bgbottom; background-image:none;";
				break;
			case 'pattern':
				$bgcss = "background: $bgbottom url('{$path}$patternurl') left top repeat;";
				break;
			case 'transparent':
				$bgcss = "background: none transparent;";
				break;
			default:
				$bgcss = "background: $bgbottom url('$bgimage') $bgpos $bgrepeat;";
		}

#		if($labelsusesamealign == 'yes') { $lalign = $talign; }
	/* 	if($labelsusesamepadding == 'yes') { $lpad = $tpad; } */
		if($labelsusesamefont) { $lfont = $tfont; $lsize = $tsize; }
		if($labelsusesamecolor) { $lcolor = $tcolor; }
		if($talign == 'center') { $blockalign = 'margin:0 auto;'; } elseif($talign == 'right') { $blockalign = 'clear:both; float:right;';}
		if($formalign == 'center') { $formalign = 'margin:0 auto;'; } elseif($formalign == 'right') { $formalign = 'clear:both; float:right;';} elseif($formalign == 'left') { $formalign = 'clear:both; float:left;';}
		if($givethanks) { $formalign .= 'margin-bottom: .5em;';}

		$safesubscribecss = '';
		$sspad = $lpad * 1.6;
		if(!empty($safesubscribe) && $safesubscribe != 'no') {
			$safesubscribecss = "$selector a.safesubscribe_{$safesubscribe} {
			background: transparent url({$path}images/safesubscribe-$safesubscribe.gif) left top no-repeat;
			$blockalign
			width:168px;
			height:14px;
			display:block;
			text-align:left!important;
			overflow:hidden!important;
			text-indent: -9999px!important;
			margin-top: {$sspad}em!important;
		}";
		}

	$paddingwidth = (int)$paddingwidth;
	$borderradius = (int)$borderradius;
	$width = (int)$width;

	$lpadbottom = round($lpad/3, 3);

$css = <<<EOD
<style type="text/css">

	<!--[if IE lte 9]>$selector { behavior: url({$path}css/border-radius.htc); }<![endif]-->

	.has_errors .cc_intro { display:none;}
	$selector .cc_success {
		margin:0!important;
		padding:10px;
		color: $tcolor!important;
	}

	$selector {
		line-height: 1;
	}
	$selector ol, $selector ul {
		list-style: none;
		margin:0;
		padding:0;
	}
	$selector li {
		list-style: none;
	}
	$selector blockquote, $selector q {
		quotes: none;
	}
	$selector blockquote:before, $selector blockquote:after,
	$selector q:before, $selector q:after {
		content: '';
		content: none;
	}

	/* remember to define focus styles! */
	$selector :focus {
		outline: 0;
	}

	$selector .req { cursor: help; }

	$selector {
		$bgcss
		padding: {$paddingwidth}px;
		$formalign
		-webkit-background-clip: border-box;
		-moz-background-clip: border-box;
		background-clip:border-box;
		background-origin: border-box;
		-webkit-background-origin: border-box;
		-moz-background-origin: border-box;
		border: $borderstyle $bordercolor {$borderwidth}px;
		-moz-border-radius: {$borderradius}px {$borderradius}px;
		-webkit-border-radius: {$borderradius}px {$borderradius}px;
		border-radius: {$borderradius}px {$borderradius}px {$borderradius}px {$borderradius}px;
		width: {$width}{$widthtype};
		color: $tcolor!important;
		font-family: $tfont!important;
		font-size: $tsize!important;
		text-align: $talign!important;
	}
	$selector * {
		font-size: $tsize;
	}
	#content $selector { margin-bottom: 1em; margin-top: 1em; }

	$selector select { max-width: 100%; }

	.kws_input_fields {
		text-align: $talign;
	}
	$selector .cc_newsletter li {
		margin:.5em 0;
	}
	$selector .cc_newsletter ul label {
		margin: 0;
		padding:0;
		line-height:1;
		cursor: pointer;
	}
	$selector input.t {
		margin: 0;
		padding:.3em;
		line-height:1.1;
		-moz-border-radius: 2px 2px;
		-webkit-border-radius: 2px 2px;
		border-radius: 2px 2px 2px 2px;
		font-family: $lfont;
		max-width: 95%;
	}
	$selector .cc_intro, $selector .cc_intro * {
		font-family: $tfont;
		margin:0;
		padding:0;
		line-height:1;
		color: $tcolor;
	}
	$selector .cc_intro * {
		padding: .5em 0;
		margin: 0;
	}
	$selector .cc_intro {
		padding-bottom:{$lpadbottom}em;
	}

	$selector .kws_input_container {
		padding-top: {$lpad}em;
	}

	$selector label {
		margin-bottom:{$lpadbottom}em;
		text-align: {$lalign};
		color: {$lcolor};
		font-size: {$lsize}px!important;
		font-family: $lfont;
		display:block;
	}


	$selector .cc_lists li { text-indent: -1.25em; padding-left: 1.4em; }
	$selector .cc_lists label { display:inline; }

	{$safesubscribecss}
	{$selector} .submit { display:block; padding-top: {$lpad}px; {$blockalign} }
	{$selector} label.kws_bold { font-weight:bold; } label.kws_bold input { font-weight:normal; }
	{$selector} label.kws_italic { font-style:italic; } label.kws_italic input { text-style:normal; }

	.kws_clear { clear:both;}
	</style>
EOD;
		$css = str_replace("\n", ' ', $css);
		$css = str_replace("\r", ' ', $css);
		$css = str_replace("\t", ' ', $css);
		return $css;
}

function getData() {
	global $debug;

	$data = $debug ? $_REQUEST : $_POST;

	return $data;
}

if(!function_exists('ctctlog')){
function ctctlog($info) {
	global $debug;
	if(!$debug) { return; }
	echo '<h4>'.$info.'</h4>';
}}
if(!function_exists('r')) {
function r($content, $die = false, $echo=true) {
	global $debug;
	if(!$debug) { return; }
	try {
		if(!function_exists('htmlentities_recursive')) {
			function htmlentities_recursive($data) {
			    foreach($data as $key => $value) {
			        if (is_array($value)) {
			            $data[$key] = htmlentities_recursive($value);
			        } else {
			        	if(is_string($value)) {
			            $data[$key] = htmlentities($value);
			            }
			        }
			    }
			    return $data;
			}
		}
		if(is_array($content)) { $content = htmlentities_recursive($content); }
		$output = '<pre style="text-align:left; margin:10px; padding:10px; background-color: rgba(255,255,255,.95); border:3px solid rgba(100,100,100,.95); overflow:scroll; max-height:400px; float:left; width:90%; max-width:800px; white-space:pre;">';
		$output .= print_r($content, true); //print_r(mixed expression [, bool return])
		$output .= '</pre>';
		if($echo) {	echo $output; } else { return $output; }
		if($die)  { die(); }
	} catch(Exception $e) {	}
}
}

function printForm() {
	global $debug;

	$data = getData();

	// Some very basic verification. Not secure, but better than nothing.
	if(
		!(@$data['verify'] === @$data['rand'] . @$data['cc-form-id'] . @$data['date']) &&

	   // Make sure the verification fields are there
	   !(isset($data['verify']) && isset($data['uniqueformid']) && isset($data['time']) && ($data['verify'] === sha1($data['uniqueformid'].$data['time'])))
	) {
		r($data['verify']);
		#r(sha1($data['uniqueformid'].$data['time']));
		ctctlog('The form was requested without authentication');
		$output = '<!-- Constant Contact: The form was requested without verification. -->';
		if(isset($data['echo'])) { echo $output; }
		return $output;
	}

	if(isset($data['f']) && is_array($data['f'])) {
		foreach($data['f'] as $key => $field) {
			if(!isset($field['pos']) || !isset($field['id'])) {
				ctctlog('pos not set for '.$key);
				unset($data['f'][$key]);
			}
		}
	}
	if(isset($data['output']) && $data['output'] == 'html') {

		if(!isset($data['form'])  && !isset($data['cc-form-id'])) {
			ctctlog('form does not exist');
			$output = '<!-- Constant Contact: The form you requested does not exist -->';
		} else {
			if(isset($data['formOnly']) && !empty($data['formOnly'])) {
				ctctlog('get formOnly');
				$output = processForm();
			} else if(isset($data['styleOnly']) && !empty($data['styleOnly']) && !empty($data['toggledesign'])) {
				ctctlog('get style Only');
				$output = processStyle();
			} else {
				ctctlog('get full form');

				$output = !empty($data['toggledesign']) ? processStyle().processForm() : processForm();
			}
		}
		if(isset($data['echo']) && !empty($data['echo'])) {
			echo $output;
			return;
		} else {
			return $output;
		}
	} else {

		ctctlog('Output not set.');

		if(isset($data['changed']) && isset($data['textOnly'])) {
			ctctlog('Text only, input changed: '.$data['changed']);
			print json_encode(array('input' => processForm()));
		} elseif(isset($data['textOnly'])) {
			ctctlog('Text only');
			print json_encode(array('form' => processForm()));
		} elseif(isset($data['styleOnly']) && !empty($data['toggledesign'])) {
			ctctlog('Style only');
			print json_encode(array('css' => processStyle()));
		} else {
			ctctlog('Whole form');
			if(!empty($data['toggledesign'])) {
				$form = processForm();
				$css = processStyle();
				ctctlog('Toggle Design set');
				print json_encode(array('css' => $css, 'form' => $form));
			} else {
				ctctlog('Design not set');
				print json_encode(array('form' => processForm()));
			}
		}
		return;
	}
}

if($debug) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}

printForm();

exit();
