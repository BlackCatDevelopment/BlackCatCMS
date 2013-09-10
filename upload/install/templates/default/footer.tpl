
	  </div>
	</div><!-- end of content -->
    <div id="buttons">
      {if $prevstep && $prevstep != 'postcheck'}
      <input type="hidden" name="prevstep" value="{$prevstep}" />
      <input accesskey="b" type="submit" id="btn_back" name="btn_back" value="&laquo; {translate('Back')}" />
      {/if}
      {if $nextstep && $status}
      <input type="hidden" name="nextstep" value="{$nextstep}" />
      <input type="submit" class="right" id="btn_next" name="btn_next" value="{translate('Next')} &raquo;" />
      {/if}
    </div>
	</form>
	<div id="black"></div>
	<img src="{$installer_uri}/templates/default/images/radial.png" alt="radial" id="radial" />
  </div> <!-- end of container -->

  <div id="header">
   <div>{translate('Installation Wizard')}</div>
  </div>
  
  <div id="footer">
    <div style="float:left;margin:0;padding:0;padding-left:50px;"><h3>enjoy the difference!</h3></div>
    <div>
      <!-- Please note: the below reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
      <a href="http://blackcat-cms.org" title="Black Cat CMS" target="_blank">Black Cat CMS Core</a> is released under the
      <a href="http://www.gnu.org/licenses/gpl.html" title="Black Cat CMS Core is GPL" target="_blank">GNU General Public License</a>.<br />
      <!-- Please note: the above reference to the GNU GPL should not be removed, as it provides a link for users to read about warranty, etc. -->
      <a href="http://blackcat-cms.org" title="Black Cat CMS Bundle" target="_blank">Black Cat CMS Bundle</a> is released under several different licenses.<br />
    </div>
  </div>
  
  <script src="{$installer_uri}/installer.js" type="text/javascript"></script>
  </body>
</html>