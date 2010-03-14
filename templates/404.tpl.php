<?php echo '<?xml version="1.0" encoding="iso-8859-15"?>'?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>.:404 Error Message:.</title>

	<script type="text/javascript" language="Javascript"><!--
	var tl=new Array(

	"Naughty, Naughty...",
	"I just have two conclusions on why you got this page",
	"1- You Have No Idea What In the World You Are Doing",
	"or,",
	"2- You Are Trying To Look At Forbidden Files, Which You have No Permission To Access",
	"If #2, you better be glad you didnt get a 403 error, b/c you dont want that",
	"Trust Me... .. .",
	"But anyways, if #1, I am sorry, ad I should be ashame of myself",
	"Why?",
	"I'm a server,",
	"Here, brains the size of the world,",
	"But when you request a page, I Cant Even Find It!",
	"I have failed at doing my job,",
	"Im supposed to serve,",
	"I feel so useless",
	"It feels like I have no meaning for my life",
	"Pathetic?",
	"What do you know about patheticness?",
	"You do not serve Millions of people around the World every day",
	"It's a hard job",
	"Dealing with bad scripts,",
	"Getting yelled at by users",
	"Not pathetic, but sad",
	"I have no other use",
	"And i can't even do what I was made for",
	"I feel so useless,",
	"Insignificant, and minuscule",
	"But NOT Pathetic",
	"Now, you click the back button",
	"And try figure out what you did",
	"If you come to this page again...",
	"ahh...",
	"you might as well click the back button again",
	"And stop trying to figure it out",
	"I dunno,",
	"E-mail the administrator or something...",
	"Tell him how it happened, and what you were doing",
	"Here you go",
	"but just keep it in the downlow...",
	"guru - at - xrogaan dot be",
	"Good luck on your Quest!",
	"I'll try to keep serving you",
	"pz... .. .",
	"",
	"",
	"",
	"",
	"",
	"",
	""
	);
	
	var speed=80; //You can chage the speed at which the characters are typed in
	var index=0; text_pos=0; //dont edit these settings
	var str_length=tl[0].length;
	var contents, row;


	function type_text()
	{
		contents='';
		row=Math.max(0,index-6);
		
		while(row<index)
			contents += tl[row++] + '\r\n';
		
		document.getElementById('textform').elements[0].value = contents + tl[index].substring(0,text_pos) + "_";
		
		if (text_pos++==str_length) {
			text_pos=0;
			index++;
			if (index != tl.length) {
			str_length=tl[index].length;
			    setTimeout("type_text()",1500); //this is how much time will pass till the next line is shown
			}
		} else
			setTimeout("type_text()",speed);
	}
	
	function MM_callJS(jsStr)
	{
		//v2.0
		 return eval(jsStr)
	}
	//--></script>

	<style type="text/css">
	body {
		color:#9E9E9E;
		background-color:#000;
	}
	textarea {
		border: thin solid #7F9DB9;
		background:#EBEBE4;
		font-family: Vrinda, Verdana, Arial, "Comic Sans MS", "Century Gothic", Adolescence;
	}
	div#main {
		margin-left: auto;
		margin-right: auto;
		text-align: center;
	}
	div#main p.little {
		font-size: 80%;
	}
	div#main p.big {
		font-size: 300%;
		font-weight: bold;
		font-family: impact;
	}
	</style>
</head>
<body onload="type_text();">
<div id="main">
	<p class="little">404</p>
	<p class="big">NOT</p>
	<p class="little">here</p>
	<form id="textform">
	<textarea cols="70" rows="8" wrap="soft" readonly="readonly" scrolling="no"></textarea>
	</form>
	<p>&nbsp;<?php echo $_SERVER['REMOTE_ADDR'] ?></p>
    <p>&nbsp;Please contact guru -at- xrogaan dot be</p>
</div>
<p>&nbsp;</p>
</body>
</html>
