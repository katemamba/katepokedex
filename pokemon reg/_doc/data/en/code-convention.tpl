<h1>Code Convention for pH7CMS</h1>
<p>When working with pH7CMS's code, developing extensions and modifications in the code, this coding convention is highly recommended for your code and have a code easy to proofreading.</p>

<h2>Classes And Interfaces: Names</h2>
<p>Name Class and Interface: UpperCamelCase (<a href="http://en.wikipedia.org/wiki/StudlyCaps">StudlyCaps</a>) and alphanumeric only.<br />
Method Name: <a href="http://en.wikipedia.org/wiki/CamelCase">camelCasee</a> and alphanumeric only.<br />
Constant Name: ALL_CAPS and alphanumeric only with the underscores to separate words.</p>


<p class="italic underline">Example:</p>

<pre>
<code>
class MyClass
{

    const MY_CONSTANT = 'abcd';

    /**
     * @param string $sVal
     * @return string Returns If the $sVal parameter is "abcd", returns "abcd", otherwise "zyxw".
     */
    public function myMethod($sVal)
    {
        if ($sVal == 'abcd')
            return 'abcd';
        else
            return 'zyxw';
    }

}
</code>
</pre>

<h4>In the pH7 Framework</h4>
<p>The classes should end with the extension ".class.php" and interfaces must end with ".interface.php".</p>

<h2>Variable Declarations: Names</h2>
<p>The variables must be in camelCasee and alphanumeric only.</p>
<p>Since PHP is not a typed language, the data found in the variables are fuzzy, so we defined a strict convention for naming variables.<br />
The first letter of the variable must define the type of this: Here is the list of available types:</p>
<p class="italic underline">Data type prefixes:</p>

<pre>
<cite>
a = Array
i = Integer
f = Float, Double
b = Boolean
c = 1 Character
s = String
by = Byte
r = Resource
o = Object
m = Mixed
</cite>
</pre>

<p>Following the first letter every word used in the Variable Name must begin with a capital letter.</p>

<p class="italic underline">Example:</p>

<pre>
<code>
touch('isSunday.txt'); // Creating an empty file
$sFile = realpath('isSunday.txt');

$iDate = date('D');
$bStatus = ($iDate == 'Sun') ? true : false;
$sValue = ($bStatus) ? 'Good Sunday' : 'We are not Sunday';

$rFile = fopen($sFile, 'w');
fwrite($rFile, $sValue);
fclose($rFile);

readfile($sFile);
</code>
</pre>

<p>We use very infrequently (or in a different way) the PEAR coding standards which requires the names of the members (methods and attributes of a class) of a private class to precede it with an underscore (_).<br />
So to distinguish between private and protected members (methods and attributes) of a class.<br />
But you can still follow this convention if you want ;-).<br />
By cons never put members of a class in public (if you do, it means that you do not know enough object-oriented programming to create a module or a code from us).<br />
Also, we rarely respect the "standard" which requires a line must not exceed 80 characters because we believe this standard and obsolete nowadays screens are larger and have a code too long can become very annoying.</p>

<h2>Functions, Global Variables and Arrays : Names</h2>
Function: lowercase and each word must be separated by underscore.

<p class="italic underline">Example:</p>

<pre>
<code>
function my_function() {}
</code>
</pre>

<p>Global variables (Session, Cookie, Global, ...): lowercase and each word must be separated by underscore.</p>

<p class="italic underline">Example:</p>

<pre><code>$GLOBALS['my_values'];</code></pre>

<p>Arrays: lowercase and each word must be separated by underscore.</p>
<p class="italic underline">Example:</p>

<pre>
<code>
$aValues = array(
   'my_key' => 'Value',
   'my_key2' => 'Value 2'
);
</code>
</pre>

or with PHP 5.4 or higher:

<pre>
<code>
$aValues = [
   'my_key' => 'Value',
   'my_key2' => 'Value 2'
];
</code>
</pre>

<p>PS: We also respect the PSR-0 and PSR-1 coding standards and we try to respect the PSR-2 coding standards but we do not always do that because some things in this new standards<br />
are not easily followed and we do not find this especially well, but you should still try to respect this standard and the PEAR standard for your modules and pieces of code.</p>
