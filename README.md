## WP-Print ##

Displays a printable version of your WordPress blog's post/page and creates pdf files of posts and pages or pdf of last 20 posts in newspaper style

## Why this fork?
needed some german language translations integrated and a slim ability to output as pdf for offline readers and a newspaper style

## Description

Once installed take the following steps to set it up:

1. WP-Print settings page is located in WP-Admin -> Settings -> Print
1. You Need To Re-Generate The Permalink (WP-Admin -> Settings -> Permalinks -> Save Changes)
1. Refer To Usage For Further Instructions

### Usage

1. Open `wp-content/themes/<YOUR THEME NAME>/index.php`. You should place it in single.php, post.php, page.php, etc also if they exist.
1. Find: `<?php while (have_posts()) : the_post(); ?>`
1. Add Anywhere Below It: `<?php if(function_exists('wp_print')) { print_link(); } ?>`

* The first value is the text for printing post.
* The second value is the text for printing page.
* Default: print_link('', '')
* Alternatively, you can set the text in 'WP-Admin -> Settings -> Print'.
* If you DO NOT want the print link to appear in every post/page, DO NOT use the code above. Just type in <strong>[print_link]</strong> into the selected post/page content and it will embed the print link into that post/page only.
* You may also add /print to the URL to see the print-friendly version
* Alternately add url parameters: ?print=1 for print and &pdfoutput=1 for last 20 posts newspaper or pdfoutput=2 for current post as pdf