<?php

	$required = $color2 = $tcolor = $lcolor = $bordercolor = $color6 = $color5 = $t = $label = $size = $name = $id = $fields = $lusc = $labelsusesamealign = $lusf = $labelsusesamepadding = $safesubscribe = $blockalign = $bgcss = $gradheight = $lpad = $lalign = $bgimage = $bgpos = $bgrepeat = $lfont = $tfont = $f = $lsize = $talign = $width = $widthtype = $borderradius = $borderwidth = $paddingwidth = $formalign = $talign = $backgroundtype = $widthtype = $borderstyle = $tsize = $lsize = '';

	$data = $this->form;
	$path = isset( $this->request['path'] ) ? $this->request['path'] : NULL;

	extract($data);

	if(isset($form)) {
		$selector = '#cc_form_'.$form;
	} else {
		$selector = 'html body div.kws_form';
	}

	$bgtop = $color6;
	$bgbottom = $color2;

	$tfont = $this->get_font_stack($tfont);
	$lfont = $this->get_font_stack($lfont);

	if($widthtype == 'per') {
		$widthtype = '%';
	}

	switch($backgroundtype) {
		case 'gradient':
			if($gradtype == 'horizontal') {
				$bgcss = <<<EOD
background: $bgtop;
background: -moz-linear-gradient(left,  $bgtop 0%, $bgbottom 100%);
background: -webkit-gradient(linear, left top, right top, color-stop(0%,$bgtop), color-stop(100%,$bgbottom));
background: -webkit-linear-gradient(left,  $bgtop 0%,$bgbottom 100%);
background: -o-linear-gradient(left, $bgtop 0%,$bgbottom 100%);
background: -ms-linear-gradient(left, $bgtop 0%,$bgbottom 100%);
background: linear-gradient(to right,  $bgtop 0%,$bgbottom 100%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#1e5799', endColorstr='#7db9e8',GradientType=1 );

EOD;
			} else {

				$bgcss = <<<EOD
background: $bgtop;
background: -moz-linear-gradient(top, $bgtop 0%, $bgbottom 100%);
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,$bgtop), color-stop(100%,$bgbottom));
background: -webkit-linear-gradient(top, $bgtop 0%,$bgbottom 100%);
background: -o-linear-gradient(top, $bgtop 0%,$bgbottom 100%);
background: -ms-linear-gradient(top, $bgtop 0%,$bgbottom 100%);
background: linear-gradient(to bottom, $bgtop 0%,$bgbottom 100%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#1e5799', endColorstr='#7db9e8',GradientType=0 );
EOD;
			}
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

		// Label uses same font
		if( $lusf ) {
			$lfont = $tfont;
		}

		// Label uses same color
		if( !empty( $lusc ) || !empty( $labelsusesamecolor ) ) {
			$lcolor = $tcolor;
		}

		// Text align inside the form
		switch( $talign ) {
			default:
			case 'center':
				$blockalign = 'margin-left: auto; margin-right: auto;';
				break;
			case 'right':
				$blockalign = 'clear:both; float:right;';
				break;
			case 'left':
				$blockalign = 'clear:both; float:left;';
				break;
		}

		// Form alignment
		switch( $formalign ) {
			default:
			case 'center':
				$formalign = 'margin-left: auto; margin-right: auto;';
				break;
			case 'right':
				$formalign = 'clear:both; float:right;';
				break;
			case 'left':
				$formalign = 'clear:both; float:left;';
				break;
		}

		$safesubscribecss = '';

		if(!empty($safesubscribe) && $safesubscribe != 'no') {
			$safesubscribecss = "
			{$selector} a.safesubscribe_{$safesubscribe} {
				background: transparent url({$path}images/safesubscribe-$safesubscribe-2x.gif) left top no-repeat;
				background-size: 100% 100%;
				{$blockalign}
				margin-top: ".($lpad)."em!important;
				width:168px;
				height:14px;
				display:block;
				text-align:left!important;
				overflow:hidden!important;
				text-indent: -9999px!important;
			}";
		}

	$paddingwidth = intval( $paddingwidth );
	$borderradius = intval( $borderradius );

	$width = intval( $width );

	$lpadbottom = round($lpad/3, 3);

$css = <<<EOD

.has_errors .cc_intro { display:none;}

{$selector} .cc_success {
	margin:0!important;
	padding:10px;
	color: {$tcolor}!important;
}

{$selector} {
	line-height: 1;
}
{$selector} ol, {$selector} ul {
	list-style: none;
	margin:0;
	padding:0;
}
{$selector} li {
	list-style: none;
}
{$selector} blockquote, {$selector} q {
	quotes: none;
}
{$selector} blockquote:before, {$selector} blockquote:after,
{$selector} q:before, {$selector} q:after {
	content: '';
	content: none;
}

{$selector} :focus {
	outline: 0;
}

{$selector} .req {
	cursor: help;
}

{$selector} {
	{$bgcss}
	padding: {$paddingwidth}px!important;
	margin-bottom: 1em; margin-top: 1em;
	{$formalign}
	-webkit-background-clip: border-box;
	-moz-background-clip: border-box;
	background-clip:border-box;
	background-origin: border-box;
	-webkit-background-origin: border-box;
	-moz-background-origin: border-box;
	border: solid {$bordercolor} {$borderwidth}px;
	-moz-border-radius: {$borderradius}px {$borderradius}px;
	-webkit-border-radius: {$borderradius}px {$borderradius}px;
	border-radius: {$borderradius}px {$borderradius}px {$borderradius}px {$borderradius}px;
	width: {$width}{$widthtype};
	max-width: 100%;
	color: {$tcolor}!important;
	font-family: {$tfont}!important;
	font-size: {$tsize}!important;
	text-align: {$talign}!important;
}
{$selector} * {
	font-size: {$tsize};
}

{$selector} select { max-width: 100%; }

.kws_input_fields {
	text-align: {$talign};
}
{$selector} li {
	margin:.5em 0;
}
{$selector} ul label {
	margin: 0;
	padding:0;
	line-height:1;
	cursor: pointer;
}
{$selector} input.t {
	margin: 0;
	padding:.3em;
	line-height:1.1;
	-moz-border-radius: 2px 2px;
	-webkit-border-radius: 2px 2px;
	border-radius: 2px 2px 2px 2px;
	font-family: {$lfont};
	max-width: 95%;
}

{$selector} ::-webkit-input-placeholder {
   color: #bbb;
}

{$selector} :-moz-placeholder {
   color: #bbb;
}

{$selector} ::-moz-placeholder {
   color: #bbb;
}

{$selector} :-ms-input-placeholder {
   color: #bbb;
}

{$selector} .cc_intro, {$selector} .cc_intro * {
	font-family: {$tfont};
	margin:0;
	padding:0;
	line-height:1;
	color: {$tcolor};
}
$selector .cc_intro * {
	padding: .5em 0;
	margin: 0;
}
{$selector} .cc_intro {
	padding-bottom:{$lpadbottom}em;
}

{$selector} .kws_input_container {
	padding-top: {$lpad}em;
}

{$selector} label {
	margin-bottom:{$lpadbottom}em;
	text-align: {$lalign};
	color: {$lcolor};
	font-size: {$lsize}px!important;
	font-family: {$lfont};
	display:block;
}


{$selector} .cc_lists li { text-indent: -1.25em; padding-left: 1.4em; }

{$safesubscribecss}
{$selector} .submit { display:block; padding-top: {$lpad}px; {$blockalign} }
{$selector} label.kws_bold { font-weight:bold; } label.kws_bold input { font-weight:normal; }
{$selector} label.kws_italic { font-style:italic; } label.kws_italic input { text-style:normal; }

.kws_clear { clear:both; }

EOD;

// If lists are hidden, hide the container.
if( isset( $list_format ) && $list_format === 'hidden' ) {
	$css .= "{$selector} .cc_lists { display: none; }";
}

echo $css;