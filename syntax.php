<?php
/**
 * Action link plugin. Lets you use action links in your wiki syntax.
 * Based on the core function - tpl_actionlink().
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 * @author nowotny <nowotnypl@gmail.com>
 * @author Chang Zhao https://github.com/chang-zhao/
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
require_once(DOKU_INC.'inc/template.php'); //mod
 
class syntax_plugin_actionlink extends DokuWiki_Syntax_Plugin {
 
    function getInfo(){
        return array(
            'author' => 'nowotny',
            'email'  => 'nowotnypl@gmail.com',
            'date'   => '2006-05-26',
            'name'   => 'Actionlink plugin',
            'desc'   => 'Actionlink plugin lets you use action links in your wiki syntax.
            			Basic syntax: {{actionlink>action|title}}',
            'url'    => 'http://dokuwiki.org/plugin:actionlink',
        );
    }
 
    function getType(){
        return 'substition';
    }
 
    function getSort(){
        return 306;
    }
 
    function connectTo($mode) {
      $this->Lexer->addSpecialPattern('\{\{actionlink>.+?\}\}',$mode,'plugin_actionlink');
    }
	
    function handle($match, $state, $pos, $handler){
		$match = substr($match,13, -2);
		$matches=explode('|', $match, 2);
		return array('action'=>hsc($matches[0]),'title'=>hsc($matches[1]));
    }
 
    function render($mode, $renderer, $data){
	if($mode == 'xhtml'){
		
		if(!empty($data['action'])) $action=$data['action'];
		else $action='';
		if(!empty($data['title'])) $title=$data['title'];
		
		global $ID;
		global $INFO;
		global $REV;
		global $ACT;
		global $conf;
		global $lang;
		global $auth;
		
		switch($action){
			case 'edit':
				#most complicated type - we need to decide on current action
				if($ACT == 'show' || $ACT == 'search'){
					if($INFO['writable']){
					if($INFO['exists']){
					if(!isset($title)) $title=$lang['btn_edit'];
					$renderer->doc .=$this->tpll_link(wl($ID,'do=edit&amp;rev='.$REV),
						$title,
						'class="action edit" accesskey="e" rel="nofollow"');
					}else{
					if(!isset($title)) $title=$lang['btn_create'];
					$renderer->doc .=$this->tpll_link(wl($ID,'do=edit&amp;rev='.$REV),
						$title,
						'class="action create" accesskey="e" rel="nofollow"');
					}
					}else{
					if(!isset($title)) $title=$lang['btn_source'];
					$renderer->doc .=$this->tpll_link(wl($ID,'do=edit&amp;rev='.$REV),
						$title,
						'class="action source" accesskey="v" rel="nofollow"');
					}
				}else{
					if(!isset($title)) $title=$lang['btn_show'];
					$renderer->doc .=$this->tpll_link(wl($ID,'do=show'),
						$title,
						'class="action show" accesskey="v" rel="nofollow"');
				}
				return true;
			case 'history':
				if(!isset($title)) $title=$lang['btn_revs'];
				$renderer->doc .=$this->tpll_link(wl($ID,'do=revisions'),$title,'class="action revisions" accesskey="o"');
				return true;
			case 'recent':
				if(!isset($title)) $title=$lang['btn_recent'];
				$renderer->doc .=$this->tpll_link(wl($ID,'do=recent'),$title,'class="action recent" accesskey="r"');
				return true;
			case ':recent':
			case '/recent':
				if(!isset($title)) $title=$lang['btn_recent'];
				$renderer->doc .=$this->tpll_link(wl('','do=recent'),$title,'class="action recent" accesskey="r"');
				return true;
			case 'index':
				if(!isset($title)) $title=$lang['btn_index'];
				$renderer->doc .=$this->tpll_link(wl($ID,'do=index'),$title,'class="action index" accesskey="x"');
				return true;
			case 'top':
				if(!isset($title)) $title=$lang['btn_top'];
				$renderer->doc .= '<a href="#dokuwiki__top" class="action top" accesskey="x">'.$title.'</a>';
				return true;
			case 'search':
				if(!isset($title)) $title=$lang['search'];
				$renderer->doc .= '<a href="javascript:dw__search.id.focus()" class="action search" accesskey="f">'.$title.'</a>';
				return true;
			case 'back':
				if ($ID = tpl_getparent($ID)) {
					if(!isset($title)) $title=$lang['btn_back'];
					$renderer->doc .=$this->tpll_link(wl($ID,'do=show'),$title,'class="action back" accesskey="b"');
					return true;
				}
				return false;
			case 'login':
				if($conf['useacl']){
					if($_SERVER['REMOTE_USER']){
					if(!isset($title)) $title=$lang['btn_logout'];
					$renderer->doc .=$this->tpll_link(wl($ID,'do=logout'),$title,'class="action logout"');
					}else{
					if(!isset($title)) $title=$lang['btn_login'];
					$renderer->doc .=$this->tpll_link(wl($ID,'do=login'),$title,'class="action logout"');
					}
					return true;
				}
				return false;
			case 'admin':
				if($INFO['perm'] == AUTH_ADMIN){
					if(!isset($title)) $title=$lang['btn_admin'];
					$renderer->doc .=$this->tpll_link(wl($ID,'do=admin'),$title,'class="action admin"');
					return true;
				}
				return false;			
			case 'backlink':
				if(!isset($title)) $title=$lang['btn_backlink'];
				$renderer->doc .=$this->tpll_link(wl($ID,'do=backlink'),$title, 'class="action backlink"');
				return true;
			case 'purge': // {{actionlink>purge|Purge}}
				if(!isset($title)) $title=$lang['btn_purge'];
				$renderer->doc .=$this->tpll_link(wl($ID,'purge=true'),$title, 'class="action purge"');
				return true;
			case 'subscribe': //mod
				if(!isset($title)) $title=$lang['btn_subscribe'];
				$renderer->doc .=$this->tpll_link(wl($ID, array("do"=>"subscribe"), false, "&"),$title,'class="action subscribe"');
				return true;
			case 'addtobook': //mod
				if(!isset($title)) $title=$lang['btn_addtobook'];
				$renderer->doc .=$this->tpll_link(wl($ID, array("do"=>"addtobook"), false, "&"),$title,'class="action addtobook"');
				return true;
			case 'cite': //mod
				if(!isset($title)) $title=$lang['btn_cite'];
				$renderer->doc .=$this->tpll_link(wl($ID, array("do"=>"cite"), false, "&"),$title,'class="action cite"');
				return true;				
			case 'infomail': //mod
				if(!isset($title)) $title=$lang['btn_infomail'];
				$renderer->doc .=$this->tpll_link(wl($ID, array("do"=>"infomail"), false, "&"),$title,'class="action infomail"');
				return true;
			case 'export_odt': //mod
				if(!isset($title)) $title=$lang['btn_export_odt'];
				$renderer->doc .=$this->tpll_link(wl($ID, array("do"=>"export_odt"), false, "&"),$title,'class="action export_odt"');
				return true;			
			case 'export_pdf': //mod
				if(!isset($title)) $title=$lang['btn_export_pdf'];
				$renderer->doc .=$this->tpll_link(wl($ID, array("do"=>"export_pdf"), false, "&"),$title,'class="action export_pdf"');
				return true;				
			default:
				if(!isset($title)) $title='';
				$this_link_start=$action{0};
				switch ($this_link_start) {
					case '/':
						$this_link_prefix='http://'.$_SERVER['SERVER_NAME']; break;
					case '#':
						$this_link_prefix=''; break;
					default:
						$renderer->doc .= $title;
						return true;
					}
				$renderer->doc .= '<a href="'.$this_link_prefix.$action.'">'.$title.'</a>';
				return true;
		}
        }
        return false;
    }
	function tpll_link($url,$name,$more=''){
		$link='<a href="'.$url.'" ';
		if ($more) $link.=' '.$more;
		$link.=">$name</a>";
		return $link;
}
}
 
//Setup VIM: ex: et ts=4 enc=utf-8 :
