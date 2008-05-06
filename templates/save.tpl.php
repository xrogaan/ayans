<div id="editor">
    <h1>Insert News</h1>
    <p><a href="index.php">back to home</a></p>
<?php
if ($this->password_fail) {
    echo "<div id=\"notice\">Can't login. Please check your password.</div>";
}
if (isset($this->updated) && $tpl->updated == true) {
?>
<p>Post fully updated.</p>
</div>
<?php
} else {
?>
    <div id="content">
        <form action="<?php echo $this->action ?>" method="post">
            <input type="text" name="title" value="<?php echo $this->input_title ?>" size="80" /><br />
            <textarea name="text" id="text" cols="80" rows="23"><?php echo $this->input_text ?></textarea>
            <p><small>
            news documents are written using <a href="http://daringfireball.net/projects/markdown/syntax">Markdown syntax</a>.
            </small></p>

            <p align="right">
                <strong>Password:</strong> <input type='password' name='password' size='20'/><br />
                <input type="submit" value="Share" />
            </p>
        </form>
    </div>
</div>
<?php
}
?>