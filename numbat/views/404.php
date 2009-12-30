<?php
/**
 * Numbat default view
 *
 * You should override this in your app/views folder
 */

class View_404 extends View_Default {
	public function render() {
		header('HTTP/1.0 404 Not Found');
?>
<!doctype html>
<!-- IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono -->
<html>
<head>
	<title>Numbat - <?php $this->output('title') ?></title>
	<link rel="stylesheet" href="<?php echo $this->config()->get('baseurl') ?>/numbat/static/style.css" />
</head>
<body>
	<div class="container">
		<?php $this->output('content', true) ?>
		<div id="footer"><p><?php echo numbat_session_stats() ?>. Powered by <a href="http://ryanmccue.info/projects/numbat">Numbat</a>.</p></div>
	</div>
</body>
</html>
<?php
	}
}