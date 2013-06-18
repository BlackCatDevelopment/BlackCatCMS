/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Website Baker Project, LEPTON Project, Black Cat Development
 *   @copyright       2004-2010, Website Baker Project
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

How to use:

1.)
put 
  require_once(CAT_PATH.'/framework/CAT/Helper/Captcha/WB/captcha.php');
in your file.


2a.)
put 
  <?php call_captcha(); ?>
into your form.
This will output a table with varying columns (3 or 4) like this example:
<table class="captcha_table"><tr>
  <td><img src="http://www.example.org/framework/CAT/Helper/Captcha/WB/captchas/ttf.php?t=64241454" alt="Captcha" /></td>
  <td><input type="text" name="captcha" maxlength="5" style="width:50px" /></td>
  <td class="captcha_expl">Fill in the result</td>
</tr></table>


2b.)
If you want to use your own layout, use additional parameters to call_captcha():
call_captcha('all') will output the whole table as above.

call_captcha('image', $style); will output the <img>-tag for the image only (or the text for an text-style captcha):
Examples:
  call_captcha('image', 'style="...; title="captcha"');
    <img style="...; title="captcha" src="http://www.example.org/framework/CAT/Helper/Captcha/WB/captchas/captcha.php?t=46784246" />
    or
    <span style="...; title="captcha">4 add 6</span>
	call_captcha('image');
    <img src="http://www.example.org/framework/CAT/Helper/Captcha/WB/captchas/captcha.php?t=46784246" />
    or
    4 add 6

call_captcha('input', $style); will output the input-field:
  call_captcha('input', 'style"...;"');
    <input type="text" name="captcha" style="...;" />
  call_captcha('input');
    <input type="text" name="captcha" style="width:50px;" maxlength="10" />

call_captcha('text', $style); will output a short "what to do"-text
  call_captcha('text', 'style="...;"');
	  <span style="...;">Fill in the result</span>
  call_captcha('text');
	  Fill in the result



The CAPTCHA-code is allways stored in $_SESSION['captcha'] for verification with user-input.
The user-input is in $_POST['captcha'] (or maybe $_GET['captcha']).
