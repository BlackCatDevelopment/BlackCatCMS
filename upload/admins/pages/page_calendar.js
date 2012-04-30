/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
 *
 */

Calendar.setup(
	{
	inputField  : start_date,
	ifFormat    : jscal_ifformat,
	button      : trigger_start,
	firstDay    : jscal_firstday,
	showsTime   : showsTime,
	timeFormat  : timeFormat,
	date        : jscal_today,
	range       : [1970, 2037],
	step        : 1
	}
);
Calendar.setup(
	{
	inputField  : end_date,
	ifFormat    : jscal_ifformat,
	button      : trigger_end,
	firstDay    : jscal_firstday,
	showsTime   : showsTime,
	timeFormat  : timeFormat,
	date        : jscal_today,
	range       : [1970, 2037],
	step        : 1
	}
);