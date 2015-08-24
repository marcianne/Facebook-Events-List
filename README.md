# Facebook-Events-List
Wordpress plugin that generates an unbranded list of facebook events to display via widget or shortcode. Includes options page and instructions. To customize appearance of individual event entries further, copy and modify fbel-template.php to the active theme directory. Uses WebDevStudios/CMB2, CMB2-Snippet-Library/options-and-settings-pages/, & mustardBees/cmb-field-select2

The reason I started this is because I haven't found a good (free) wp plugin solution that is both unbranded and flexible enough to use across a variety of projects without a lot of hackery.  I've also had lots of issues with plugin breaks & incorrect timezone display after updates. 

Next Steps:
<ul>
<li>Fix Date Sorting so that events occur in calendar order.</li>
<li>Fix locations display to handle incomplete data.</li>
<li>Add auto-update.</li>
<li>Settle on a more practical & descriptive name.</li>
<li>Work on more practical time zone picker.</li>
<li>Re-organize and rename functions consistently.</li>
<li>Build in display options</li>
<li>Include instructions and diagram of CSS selectors & organization of divs, etc. </li>
<li>Possibly integrate with some sort of js calendar plugin, like FullCalendar</li>
<li>Eliminate unnecessary procedural aspects</li>
</ul>


To give credit where it's due, here are some of the many sources I consulted before cribbing this together:

<ul>
<li>https://www.codeofaninja.com/2011/07/display-facebook-events-to-your-website.html</li>
<li>https://github.com/WebDevStudios/CMB2</li>
<li>https://github.com/mustardBees/cmb-field-select2</li>
<li>https://github.com/WebDevStudios/CMB2-Snippet-Library</li>
<li>https://getcomposer.org/</li>
<li>https://developers.facebook.com/docs/graph-api</li>
<li>https://www.sammyk.me/optimizing-request-queries-to-the-facebook-graph-api</li>
<li>http://code.tutsplus.com/tutorials/distributing-your-plugins-in-github-with-automatic-updates--wp-34817</li>
</ul>
