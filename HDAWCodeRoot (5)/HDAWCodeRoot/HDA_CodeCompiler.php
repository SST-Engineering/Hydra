<?php

  
class HDA_ICode_Exception extends Exception {}
$glob_mem = "";

global $HDA_code_keywords;
$HDA_code_keywords = 
    array(	'ALL', 'AND', 'AS', 
		'BEGIN', 
		'CASE', 
		'DEFAULT', 'DO',
		'EACH', 'EXIT', 'END', 'ELSE', 'EMPTY', 'EXISTS', 'EXTENDS',
		'FOR', 'FALSE', 'FUNCTION',
		'GE', 'GREATER_THAN_OR_EQUAL', 'GT', 'GREATER_THAN',
		'HAS',
		'IF','IN', 'IS',
		'LE', 'LESS_THAN_OR_EQUAL', 'LT', 'LESS_THAN', 'LIMIT', 'LITERAL', 'LIKE',
		'NOT', 'NUMBER', 'NULL',
		'OR',
		'PROPERTY','PROPERTIES',
		'RETURN',
		'STRING', 'STEP', 'STATE', 'SKIP',
		'THEN', 'TRUE', 'TO',
		'UNSET', 'UNTIL',
		'WHEN', 'WHILE'
		);

$HDA_code_directives = 
   array( 'COMMON', 'HDOCS', 'METATAGS' );

   
class _token_code {
private $s = "";
private $s_len = 0;
private $s_ptr = 0;
private $toks = array();
private $el_toks = array();
private $tok = null;
private $token_parse = "";
private $error = array();

//private $dbg_on = "%%%";
//private $dbg_off = "--%%%";
const dbg_on = "\x01";
const dbg_off = "\x02";

private $symbols = array('+','-','*','/','%',')','(','=','.',',','<','>','?');
private $logops = array('<','>','<=','=<','=>','>=');
private $concops = array('+=','-=');
private $stateops = array('->');
private $nops = array('|');
private $nopstmt = array(';');
private $dbgsw = array();

public $for_layout = false;

public function __destruct() {
   unset($this->s);
   $this->s = null;
   unset($this->toks);
   $this->toks = null;
   unset($this->el_toks);
   $this->el_toks = null;
   global $glob_mem;
   $glob_mem .= "destruct token_code ".memory_get_usage(true)."\n";
   }
public function __construct($s, $layout=false) {
   global $glob_mem;
   $glob_mem .= "construct token_code ".memory_get_usage(true)."\n";
   $this->for_layout = $layout;
   $this->s = ($layout)?$this->_build_layout_s_($s):$this->_build_s_($s);
   $this->s_len = strlen($this->s);
   $this->dbgsw = array(self::dbg_on,self::dbg_off);
   $this->token_parse .= "/";
   $this->token_parse .= "({self::dbg_off})"; // common block end
   $this->token_parse .= "|";
   $this->token_parse .= "({self::dbg_on})"; // common block start
   $this->token_parse .= "|";
   $this->token_parse .= "(#[\w_]*)"; // external vars
   $this->token_parse .= "|";
   $this->token_parse .= "(\\\$[\w_]*)"; // alt internal
   $this->token_parse .= "|";
   $this->token_parse .= "([\d]*)"; // int number
   $this->token_parse .= "|";
   $this->token_parse .= "([\w_]*)"; // name, keyword, function
   $this->token_parse .= "|";
   $this->token_parse .= "([+\-<>=][<>=])"; // double char sym
   $this->token_parse .= "|";
   $this->token_parse .= "([^\w])"; // single char sym
   $this->token_parse .= "/";
   }
   private $_line = "";
   
   private function _build_layout_s_($s) {
     $s = str_replace(array("\xe2\x80\x9c","\xe2\x80\x9d","\xe2\x80\x98","\xe2\x80\x99"), array("\"","\"","'","'"), $s);
     $s = str_replace(array("\r\n","\r","\n"),"~",$s);
	 return $s;
     }
   private function _build_s_($s, $insert_dbg=true) {
      $s = $this->_build_layout_s_($s);
      $s = preg_replace_callback("#common[\s]*\"(?P<inclfile>[^\"]*)\"[\s]*[;~]*#i",'_token_code::_insert_common',$s);
      $s = preg_replace_callback("#hdocs[\s]*\"(?P<hdoc>[^\"]*)\"#i",'_token_code::_skip_hdocs',$s);
	  $s = preg_replace_callback("#metatags[\s]*\"(?P<metatags>[^\"]*)\"#i",'_token_code::_capture_metatags',$s);
      return ((!$insert_dbg)?self::dbg_on:"").$s.((!$insert_dbg)?self::dbg_off:"");
      }


   public function parse() {
      while (($c = $this->st()) !== false) {
	     switch ($c) {
		    case "~":
			   $this->emit_nl();
			   break;
		    case " ":
			case "\t":
			   $this->emit();
			   break;
			case "\"":
			case "'":
			   $this->emit_s($c);
			   break;
			case "{":
			   $this->emit_lit();
			   break;
			case "/":
			   $this->emit_comment();
			   break;
			default:
			   $this->tok .= $c;
			   break;
		    }
	     }
	  $this->emit();
      if (count($this->toks)==0) return true;
      foreach ($this->toks as $tok) {
         $t = $this->what_is($tok);
         $this->el_toks[] = $t;
         }
      return (count($this->error)==0)?$this->el_toks:false;
      }
   
   
   private function st() {
      if ($this->s_ptr < $this->s_len) { $c = $this->s[$this->s_ptr++]; $this->_line.=$c; return $c; }
	  return false;
      }
   private function st_retard() {
      if ($this->s_ptr>0) {
	     $this->_line = substr($this->_line,0,-1);
	     $this->s_ptr--;
		 }
	  }
   private function _emit() {
      if (!is_null($this->tok)) {
	     $this->toks[] = $this->tok;
		 $this->tok = null;
		 }
      }
   private function emit() {
      if (!is_null($this->tok)) {
	     $s_toks = @preg_split($this->token_parse, $this->tok, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
         if (count($s_toks)==0) $this->toks[] = $this->tok;
         else foreach ($s_toks as $tok) $this->toks[] = $tok;
		 $this->tok = null;
		 }
	  }
   private function emit_nl() {
      if ($this->for_layout) { $this->emit(); $this->toks[] = '~';}
      else {
	   //  if (!is_null($this->tok)) 
	     $this->emit();
	     $this->_line = ""; 
		 $this->toks[] = '|';
		 }
      }
   private function emit_s($c) {
      $this->emit();
	  $this->tok .= $c;
	  while (($cc=$this->st()) !== false) {
	     switch ($cc) {
		    case "\\":
			   $cc = $this->st();
			   if ($this->for_layout) {
			      $this->tok .= "\\";
				  $this->tok .= $cc;
				  }
			   else switch ($cc) {
			      case "\"":
				  case "'":
				  case "\\":
				     $this->tok .= $cc;
					 break;
				  case "n":
				     $this->tok .= chr(0x0A);
					 break;
				  case "t":
				     $this->tok .= chr(0x09);
					 break;
				  default:
				     $this->tok .= "\\";
				     $this->tok .= $cc;
					 break;
			      }
			   break;
			case "\"":
			case "'":
			   if ($cc == $c) { $this->tok .= $cc; $this->toks[] = $this->tok; $this->tok = null; return; }
			default:
			   $this->tok .= $cc;
		   }
	     }
	  $this->tok .= $c;
	  $this->_emit();
      }
   private function emit_lit() {
      $this->emit();
	  $this->tok .= "{";
	  $level = 0;
	  while (($cc=$this->st()) !== false) {
	     switch ($cc) {
	        case "{": $level++; break;
		    case "}": 
		       if ($level==0) {
		          $this->tok .= $cc; 
				  $dbg_toks = substr_count($this->tok, '~');
				  $this->tok = str_replace('~',"\n",$this->tok);
				  $this->toks[] = $this->tok; 
				  $this->tok = null; 
				  $this->_line = substr($this->_line,0, strpos($this->_line,'{')+20); 
				  while ($dbg_toks>0) { $this->toks[] = '|'; $dbg_toks--;  }
				  return;
			      }
			   else $level--;
			   break;
			case "|":
				$cc = "£";
				break;
			}
		  $this->tok .= $cc;
		  }
	   $this->parse_error("Unterminated literal statement ",substr($this->tok, 0, 50));
	   $this->tok .= "}";
	   $this->_emit();
	   }
	private function emit_comment() {
	   $this->emit();
	   $this->tok .= "/";
	   $cc = $this->st();
	   $this->tok .= $cc;
	   if ($cc == '/') {
	      $this->copy_to('~');
		  $this->_line = "";
		  }
	   else if ($cc=='*') {
	      while (($cc=$this->st()) !== false) {
		     $this->tok .= $cc;
		     if ($cc=='*') { 
			    if ($this->st()=='/') { $this->tok .= '/'; break; }
				$this->st_retard();
				}
			 }
		  $this->tok = str_replace('~',"\n",$this->tok);
		  $this->_line = "";
	      }
	   else {
	      $this->tok = "/";
	      $this->st_retard();
		  $this->emit();
	      }
	   $this->tok = ($this->for_layout)?$this->tok:null;
	   $this->_emit();
	   return;
	   }
	private function copy_to($c) {
	   while (($cc=$this->st())!==false && $cc!=$c) $this->tok .= $cc;
	   $this->tok .= $c;
	   }
   private $common_files = array();
   private function _insert_common($matches) {
      global $common_code_dir;
      $s = "";
      if (array_key_exists('inclfile', $matches)) {
         if (!array_key_exists(strtolower($matches['inclfile']), $this->common_files)) {
		    $incl_path = "common/{$matches['inclfile']}";
			if (!file_exists($incl_path)) {
               $incl_path = "{$common_code_dir}/{$matches['inclfile']}";
               if (!file_exists($incl_path)) $this->parse_error("Can't find common file {$matches['inclfile']}", $matches[0]);
			   }
            if (file_exists($incl_path)) {
               $this->common_files[strtolower($matches['inclfile'])] = true;
               $s = file_get_contents($incl_path);
               $s = $this->_build_s_($s, $insert_dbg=false);
               }
            }
         }
      return $s;
      }
   public $hdocs = array();
   private function _skip_hdocs($matches) {
      $s = "";
      if (array_key_exists('hdoc', $matches)) {
         if (!array_key_exists(strtolower($matches['hdoc']), $this->hdocs)) {
            $this->hdocs[strtolower($matches['hdoc'])] = str_replace("_"," ",$matches['hdoc']);
            }
         }
      return $s;
      }
   public $metatags = "";
   private function _capture_metatags($matches) {
      if (array_key_exists('metatags', $matches)) $this->metatags .= "{$matches['metatags']};";
	  return "";
	  }
   private function what_is($t) {
      global $HDA_code_keywords;
      if (strlen($t)==0) return array('NULL',"");
      if ($t[0]=="\"" || $t[0]=="'") {
         return array('STRING', substr($t, 1, strlen($t)-2)); //trim($t,$t[0]));
         }

      if (strlen($t)>1 && $t[0]=="/" && $t[1]=="/") return array('LCOMMENT',$t);
      if (strlen($t)>1 && $t[0]=="/" && $t[1]=="*") return array('MCOMMENT',$t);
      

      if (in_array(strtoupper($t), $HDA_code_keywords)) return array('KEY',strtoupper($t));
      if (in_array($t, $this->symbols)||in_array($t, $this->logops)||in_array($t, $this->concops)||in_array($t, $this->stateops)) return array('SYM',$t);
      if (strlen($t)>1 && $t[0]=='#') return array('GVAR',$t);
      if (strlen($t)>1 && $t[0]=='$') return array('VAR',$t);
      if ($t[0]=='{') return array('LIT', $t);
      if (is_numeric($t)) return array('INT',$t);
      if (preg_match('/[\w_]/',$t[0])==1) return array('NAME',$t);
      if (in_array($t, $this->nopstmt)) return array('NOPSTMT',$t);
      if (in_array($t, $this->nops)) return array('NOP',$t);
      if (in_array($t, $this->dbgsw)) return array('DBG',$t);
      return array('OTHER',$t);
      }

   private function parse_error($e, $t) {
      $this->error[] = "Parse error {$e} near ..\"{$t}\"..";
      }
   public function errors() {
      return (count($this->error)>0)?$this->error:null;
	  }
		  
   
}

class _layout_code {
   private $toks = array();
   private $on_tok = 0;
   private $_layout_inset = 0;
   private $_layout_nl = 0;
   private $_layout_sp = 0;
   private $_layout_ss = "";
   
   public $_tokenizer = null;
   
   public function __construct($s) {
      $this->_tokenizer = new _token_code($s, true);
	  $this->toks = $this->_tokenizer->parse();
	  }
   public function layout() {
      return $this->layout_s();
	  }
   private function _layout_line($force=false) {
      if ($this->_layout_nl==0 || $force) {
         $this->_layout_ss .= $this->_layout_line_s();
         $this->_layout_sp = false;
         }
      $this->_layout_nl++; 
      }
   private function _layout_line_s() {
      $s = "";
      $s .= "\n";
      $i = $this->_layout_inset;
      while ($i-- > 0) $s .= "   ";
      return $s;
      }
   private function _trim_nl() {
      if ($this->_layout_nl>0 && $this->_layout_ss[strlen($this->_layout_ss)-1]=="\n") {
         $this->_layout_ss[strlen($this->_layout_ss)-1] = " ";
         }
      }
   private function _layout_plant($s, $pre_sp=null, $post_sp=null) {
      if ($this->_layout_sp!==false && $pre_sp!==false) $this->_layout_ss .= " ";
      $this->_layout_ss .= $s;
      $this->_layout_sp = $post_sp;
      $this->_layout_nl = 0;
      }

   public function layout_s() {
      foreach ($this->toks as $t) {
         switch ($t[0]) {
            case 'OTHER':
               switch ($t[1]) {
                  case '~': $this->_layout_line(); break;
                  case ' ': break;
                  default: $this->_layout_plant($t[1]); break;
                  }
               break;
            case 'STRING':
               $this->_layout_plant("\"{$t[1]}\"");
               break;
            case 'LCOMMENT':
            case 'MCOMMENT':
               $c = $t[1];
               $c = preg_replace("/\n[\s]*/","~",trim($c,"~"));
               $c = str_replace('~',$this->_layout_line_s(), $c);
               $this->_layout_plant($c);
               $this->_layout_line();
               break;
            case 'VAR':
            case 'GVAR':
               $this->_layout_plant($t[1], true, true);
               break;
            case 'NAME':
               $this->_layout_plant($t[1], true, true);
               break;
            case 'KEY':
               $t[1] = strtolower($t[1]);
               switch ($t[1]) {
                  case 'begin': $this->_trim_nl(); $this->_layout_plant(" {$t[1]}", true, false); $this->_layout_inset++; $this->_layout_line(); break;
                  case 'end': $this->_layout_line(); $this->_layout_plant($t[1]); $this->_layout_inset--; $this->_layout_line(); break;
                  case 'function': $this->_layout_line(true); $this->_layout_line(true); $this->_layout_plant($t[1], true, true); break;
                  default: $this->_layout_plant($t[1], true, true); break;
                  }
               break;
            case 'SYM':
               switch ($t[1]) {
			  case ',':
				$this->_layout_plant($t[1], false, true); break;
                  case '(': 
                  case ')': $this->_layout_plant($t[1], false, false); break;
                  case '.': $this->_layout_plant($t[1], false, false); break;
                  default: $this->_layout_plant($t[1], true, true);
                  }
               break;
            case 'NOPSTMT':
               $this->_layout_plant($t[1], false, true);
               break;
			case 'DBG':
			case 'NOP':
			   break;
            default:
               $this->_layout_plant($t[1]);
               break;
            }
         }
      $this->_layout_ss = str_replace('~',"\n",$this->_layout_ss);
      return $this->_layout_ss;
      }

}



class _parse_code {
   private $token = array('NULL',null);

   private $toks = array();
   private $on_tok = 0;
   private $error = array();
   private $debug_token = 1;
   private $plant_debug = 0;
   private $execute = array();
   private $marks = array();
   private $fns = array();
   private $exit_label = 0;
   
   private $EOF_token = array('EOF',"");
   private $NULL_token = array('NULL',null);
   private $NOP = array('NOP',"");
   private $EQ = array('COND',"EQ");

   public $_tokenizer = null;
   
   public function __destruct() {
      unset($this->_tokenizer);
	  $this->_tokenizer = null;
	  unset($this->execute);
	  $this->execute = null;
	  unset($this->toks);
	  $this->toks = null;
      global $glob_mem;
      $glob_mem .= "destruct parse_code ".memory_get_usage(true)."\n";
      }
   public function __construct($s) {
      global $glob_mem;
      $glob_mem .= "construct parse_code ".memory_get_usage(true)."\n";
      $this->_tokenizer = new _token_code($s);
	  $this->toks = $this->_tokenizer->parse();
	  if ($this->toks===false) $this->error = $this->_tokenizer->errors();
      $this->on_tok = -1;
      $this->advance();
      $this->execute = array();
      $this->marks = array();
      $this->fns = array();
      $this->exit_label = array('LBL', $this->next_label());
      }
   public function compile() {
      if (count($this->error)>0) return false;
      while ($this->stmt()) {  }
	  if (count($this->execute)==0) return $this->syntax_error($this->NULL_token, "File generated no code");

      $this->plant('PUSH', array('NOP',true));
      $this->plant('LBL', $this->exit_label); $this->plant('EXIT', $this->NOP);
	  return (count($this->error)==0)?array('icode'=>$this->execute, 'fns'=>$this->fns):false;
      }
   public function compile_string($caller) {
      if (count($this->error)==0) {
	     $this->on_label = $caller->next_label();
         $this->expression();
		 $caller->on_label = $this->next_label();
         if ($this->token[0] <> 'EOF') $this->syntax_error($this->token, "Invalid characters after expression ".print_r($this->toks,true).print_r($this->execute,true));
         }
      return (count($this->error)==0 && count($this->execute)>0)?$this->execute:NULL;
      }
	  
   private function advance() {
      if ($this->token[0]=='EOF') return false;
      $this->on_tok++;
      if ($this->on_tok < count($this->toks)) {
         $this->token = $this->toks[$this->on_tok];
		 if ($this->token[0]=='DBG') {
		    $this->plant_debug += ($this->token[1]==_token_code::dbg_on)?1:-1;
			return $this->advance();
			}
		 if ($this->token[0]=='NOP') {
		    if ($this->plant_debug==0) $this->debug_token++;
			return $this->advance();
		    }
         }
      else { $this->token = $this->EOF_token; }
      return true;
      }
   private function retard() {
      $this->on_tok--;
      if ($this->on_tok >= 0) {
         $this->token = $this->toks[$this->on_tok];
		 if ($this->token[0]=='DBG') {
		    $this->plant_debug += ($this->token[1]==_token_code::dbg_on)?-1:1;
			return $this->retard();
			}
		 if ($this->token[0]=='NOP') {
		    if ($this->plant_debug==0) $this->debug_token--;
			return $this->retard();
		    }
         }
      else { $this->token = $this->EOF_token; return false; }
      return true;
      }
   public $on_label = 0;
   private function next_label() {
      $this->on_label++;
      return $this->on_label;
      }
// Syntax

   private function stmt() {
      if ($this->is_eof()) return false;
      if ($this->function_def()) {
         return true;
         }
      elseif ($this->action()) return true;
      $this->syntax_error($this->token,"Invalid statement");
      return false;
      }

   private function if_stmt() {
      if ($this->keyword_is('IF') && $this->advance()) {
         $label = array('LBL', $this->next_label());
         if ($this->expression()) {
            if ($this->keyword_is('THEN')) $this->advance();
            if ($this->keyword_is('DO')) $this->advance();
            }
         $this->plant('TST',$label); 
         if ($this->action()) {
            if ($this->keyword_is('ELSE') && $this->advance()) {
               $skip_else = array('LBL', $this->next_label());
               $this->plant('JMP', $skip_else);
               $this->plant('LBL', $label);
               if ($this->action()) {
                  $this->plant('LBL', $skip_else);
                  }
               else $this->syntax_error($this->token, "Invalid ELSE clause in IF statement");
               }
            else $this->plant('LBL', $label);
            return true;
            }
         $this->syntax_error($this->token, "Invalid IF statement");
         }
      return false;
      }


   private function action() {
      if ($this->nop_stmt()) return true;
      elseif ($this->plant_debug==0) $this->plant('DBG', array('DBG',$this->debug_token));
      if ($this->code_block() || 
          $this->function_call() ||
          $this->if_stmt() ||
          $this->assignment() || 
          $this->return_stmt() ||
          $this->while_block() || 
		  $this->case_stmt() ||
          $this->for_stmt() ||
          $this->lit_stmt() ||
          $this->unset_stmt() ||
		  $this->state_def_stmt() ||
		  $this->state_set_stmt() ||
          $this->exit_stmt() ||
          $this->nop_stmt()
          ) {
         return true;
         }
      return false;
      }

   private function function_def() {
      if ($this->keyword_is('FUNCTION') && $this->advance()) {
         $label = array('LBL', $this->next_label());
         $enter = array('LBL', $this->next_label());
         $this->plant('JMP', $label);
         $this->plant('LBL', $enter);
         $msg = "missing function name";
         if ($this->is_name()) {
            $fn_name = $this->token;
            $this->fns[$this->token[1]] = $enter;
            $this->advance();
            $msg = "requires argument list or ()";
            if ($arg_list = ($this->symbol_is('(')&& $this->advance())) {
               $args = array();
               while ($arg_list) {
                  if ($this->is_var()) {
                     $args[] = $this->token;
                     $arg_list = ($this->advance() && $this->symbol_is(',') && $this->advance());
                     }
                  else $arg_list = false;
                  }
               $msg = "closing argument list";
               if ($this->symbol_is(')') && $this->advance()) {
                  $label_except = array('LBL', $this->next_label());
                  $this->plant('PUSH', array('INT',count($args)));
                  $this->plant('CMP', $this->EQ);
                  $this->plant('TST', $label_except);
                  $this->plant('STNS', array('NOP', $fn_name[1]));
                  while (count($args)>0) {
                     $this->plant('GET', array_pop($args));
                     $this->plant('SWP', $this->NOP);
                     $this->plant('PUT', $this->NOP);
                     }
                  $msg = "invalid function body";
                  if ($this->stmt()) {
                     $this->plant('PUSH', array('INT',0));
                     $this->plant('RTN', $this->NOP);
                     $this->plant('LBL', $label_except);
                     $this->plant('PUSH', $fn_name);
                     $this->plant('EXCP', array('EXCP', 'ARGC'));
                     $this->plant('EXIT', $this->NOP);
                     $this->plant('LBL', $label);
                     return true;
                     }
                  }
               }
            }
         $this->syntax_error($this->token, "Error in function definition {$msg}");
         }
      return false;
      }

   private function nop_stmt() {
      return ($this->is_nopstmt() && $this->advance());
      }
	  
   private function case_stmt() {
      if ($this->keyword_is('CASE') && $this->advance()) {
         $end_label = array('LBL', $this->next_label());
	     if ($this->expression()) {
            if ($this->keyword_is('BEGIN')) $this->advance();
			while ($this->keyword_is('WHEN') && $this->advance()) {
			   $skip_lbl = array('LBL', $this->next_label());
			   $do_lbl = array('LBL', $this->next_label());
			   if ($this->expression()) {
			      $this->plant('CASE', $this->NOP);
				  $this->plant('TST', $do_lbl);
				  while ($this->symbol_is(',') && $this->advance()) {
				     if ($this->expression()) {
			            $this->plant('CASE', $this->NOP);
				        $this->plant('TST', $do_lbl); 
					    }
					 else { $this->syntax_error($this->token, "Invalid expression list in WHEN clause"); break; }
				     }
				  $this->plant('JMP', $skip_lbl);
				  $this->plant('LBL', $do_lbl);
				  $this->plant('POP', $this->NOP);
			      if ($this->action()) {
				     $this->nop_stmt();
					 $this->plant('JMP', $end_label);
					 $this->plant('LBL', $skip_lbl);
				     }
				  else { $this->syntax_error($this->token, "Expected statement to execute after WHEN expression tested"); break; }
				  }
			   else { $this->syntax_error($this->token, "Expected expression to test after WHEN"); break; }
			   }
		    while ($this->action()) {} // default stmts
			$this->plant('LBL', $end_label);
			if ($this->keyword_is('END') && $this->advance()) return true;
			$this->syntax_error($this->token, "Expected END to terminate CASE statement");
		    }
		 else $this->syntax_error($this->token, "Expression expected after CASE");
         }
	  return false;
      }
	  
   private function state_def_item($state_on) {
      $state_from = $state_to = null; $state_back = $this->NULL_token; $state_op = 'DSTT';
	  if ($this->keyword_is('STEP')) $this->advance();
	  elseif ($this->keyword_is('SKIP')) { $state_op = 'SSTT'; $this->advance(); }
	  if ($this->is_name() || $this->keyword_is('NULL') || $this->symbol_is('*')) {
	     $state_from = ($this->keyword_is('NULL'))?$this->NULL_token:$this->token;
		 $state_to = $state_from;
	     $this->advance();
	     if ($this->keyword_is('THEN') || $this->is_state_op() || $this->symbol_is('+')) $this->advance();
	     if ($this->is_name() || $this->keyword_is('NULL')) {
	        $state_to = ($this->keyword_is('NULL'))?$this->NULL_token:$this->token;
		    $this->advance();
			}
		 if ($this->keyword_is('ELSE') || $this->is_state_op() || $this->symbol_is('-')) {
		    $this->advance();
			if ($this->is_name() || $this->keyword_is('NULL')) {
			   $state_back = ($this->keyword_is('NULL'))?$this->NULL_token:$this->token;
			   $this->advance();
			   }
		    else $this->syntax_error($this->token, "Invalid form of STATE statement expected a NAME or keyword NULL for backtrack state");
		    }
	     if ($this->keyword_is('WHEN')) $this->advance();
		 if ($this->expression()) {
	        $this->plant('PUSH',$state_back);
			$this->plant('PUSH',$state_to);
			$this->plant('PUSH',$state_from);
			$this->plant($state_op, $state_on);
		    return true;
		    }
		 else $this->syntax_error($this->token, "Invalid form of STATE statement expected an expression for the pattern");
	     }
	   else $this->syntax_error($this->token, "Invalid form of STATE statement expected a NAME or keyword NULL for originating state");
	   return false;
	   }
	  
   private function state_def_stmt() {
      if ($this->keyword_is('STATE') && $this->advance()) {
	     if ($this->is_name()) {
		    $state_on = $this->token;
			$this->advance();
			if ($this->keyword_is('EXTENDS') && $this->advance()) {
			   if ($this->expression()) {
			      $this->plant('STAE', $state_on);
				  return true;
			      }
			   else $this->syntax_error($this->token, "Expression that resolves to a State Name expected after STATE {$state_on[1]} EXTENDS");
			   }
			elseif ($this->keyword_is('BEGIN') && $this->advance()) {
			   while (!$this->keyword_is('END') && ($ok = $this->state_def_item($state_on)) && $this->advance());
			   $this->advance();
			   return $ok;
			   }
			else return $this->state_def_item($state_on);
			}
		 else $this->syntax_error($this->token, "Simple NAME expected after STATE to define state machine");
	     }
      return false;
	  }
	  
   private function state_set_stmt() {
      if ($this->state_term()) {
	     $this->plant('POP', $this->NOP);
		 return true;
		 }
	  return false;
      }

   private function return_stmt() {
      if ($this->keyword_is('RETURN') && $this->advance()) {
         if ($this->expression()) {
            $this->plant('RTN', $this->NOP);
            return true;
            }
         else $this->syntax_error($this->token, "Expression missing after RETURN");
         return false;
         }
      return false;
      }

   private function unset_stmt() {
      if ($this->keyword_is('UNSET') && $this->advance()) {
         $op = 'P';
         if ($this->is_keyword()) {
            switch ($this->token[1]) {
               case 'ALL': $op = 'A'; $this->advance(); break;
               case 'STRING': $op = 'S';  $this->advance(); break;
               case 'NUMBER': $op = 'N';  $this->advance(); break;
               }
            }
         if ($this->keyword_is('PROPERTIES')) $this->advance();         
         if ($this->keyword_is('PROPERTY')) $this->advance();
         if ($op=='P') {
            if ($this->variable() && $this->advance()) {
               $this->plant('EVAL', $this->NOP);
               }
            elseif ($this->is_num() || $this->is_string() || $this->is_name()) {
               $this->plant('PUSH', $this->token);
               $this->advance();
               }
            else {
               $this->syntax_error($this->token,"UNSET parse error, expects variable, number, string or name before IN");
               return false;
               }
            }
         if ($this->keyword_is('IN')) $this->advance();
         if ($this->state_as_var() || $this->variable()) {
            $this->advance();
            $this->plant('USET', array('USET',$op));
            return true;
            }
         else {
            $this->syntax_error($this->token,"UNSET parse error, expects target variable to unset after IN");
            return false;
            }
         }
      return false;
      }

   private function lit_stmt() {
      if ($this->keyword_is('LITERAL') && $this->advance()) {
         if ($this->expression()) {
            }
         elseif ($this->is_literal()) {
            $this->plant('PUSH', $this->token);
            $this->advance();
            }
         else {
            $this->syntax_error($this->token, "LITERAL expects a string in {...} or a string expression");
            return false;
            }
         if ($this->keyword_is('TO') && $this->advance()) {
            if ($this->expression()) {
               $this->plant('LIT', $this->NOP);
               return true;
               }
            else $this->syntax_error($this->token, "LITERAL invalid TO clause expects an expression to resolve to a path");
            }
         else $this->syntax_error($this->token, "LITERAL expects a TO clause to follow {...} or the string expression");
         }
      return false;
      }

   private function exit_stmt() {
      if ($this->keyword_is('EXIT') && $this->advance()) {
         if (!$this->expression()) $this->plant('PUSH', array('NOP',true));
         $this->plant('JMP', $this->exit_label);
         return true;
         }
      return false;
      }

   private function for_stmt() {
      if ($this->keyword_is('FOR') && $this->advance()) {
         if ($this->keyword_is('LIMIT') && $this->advance()) {
            if (!$this->expression()) {
               $this->syntax_error($this->token,"LIMIT clause in FOR statement requires an expression or value");
               return false;
               }
            }
         else $this->plant('PUSH', array('NOP',null));
         $start_lbl = array('LBL', $this->next_label());
         $skip_lbl = array('LBL', $this->next_label());
		 if ($this->simple_variable()) {
		    $tst_lbl = array('LBL', $this->next_label());
		    $for_var = $this->token;
			$this->advance();
			if ($this->keyword_is('IS') && $this->advance()) {
			   if (!$this->expression()) {
			      $this->syntax_error($this->token,"FOR {$for_var[1]} IS then expects an initial value expression followed by STEP expression UNTIL/TO expression");
				  return false;
			      }
			   $this->plant('PUT', $this->NOP);
			   if (!($this->keyword_is('STEP') && $this->advance() && $this->expression())) {
			      $this->syntax_error($this->token,"FOR {$for_var[1]} IS expression then expects STEP expression UNTIL/TO expression");
				  return false;
			      }
			   $this->plant('JMP', $tst_lbl);
			   $this->plant('LBL', $start_lbl);
			   $this->plant('SWP', $this->NOP);
               $this->plant('TLMT', $skip_lbl);
			   $this->plant('SWP', $this->NOP);
			   $this->plant('DUP', $this->NOP);
			   $this->plant('GET', $for_var);
			   $this->plant('SWP', $this->NOP);
			   $this->plant('GET', $for_var);
			   $this->plant('EVAL', $this->NOP);
			   $this->plant('OP', array('OP','+'));
			   $this->plant('PUT', $this->NULL_token);
			   $this->plant('LBL', $tst_lbl);
			   $tst_to_eq = ($this->keyword_is('UNTIL'))?'E':'T';
			   if (!(($this->keyword_is('UNTIL')||$this->keyword_is('TO')) && $this->advance() && $this->expression())) {
			      $this->syntax_error($this->token,"FOR {$for_var[1]} IS expression STEP expression then expects UNTIL/TO expression");
				  return false;
			      }
			   $this->plant('SWP', $this->NOP);
			   $this->plant('DUP', $this->NOP);
			   $this->plant('PUSH', array('NOP', 0));
			   $alt_cmp = array('LBL', $this->next_label());
			   $skip_alt_cmp = array('LBL', $this->next_label());
			   $this->plant('NCMP', array('COND', "LT"));
			   $this->plant('TST', $alt_cmp);
			   $this->plant('SWP', $this->NOP);
			   $this->plant('GET', $for_var);
			   $this->plant('EVAL', $this->NOP);
               $this->plant('NCMP', array('COND',"L{$tst_to_eq}"));
               $this->plant('TST', $skip_lbl);
			   $this->plant('JMP', $skip_alt_cmp);
			   $this->plant('LBL', $alt_cmp);
			   $this->plant('SWP', $this->NOP);
			   $this->plant('GET', $for_var);
			   $this->plant('EVAL', $this->NOP);
               $this->plant('NCMP', array('COND',"G{$tst_to_eq}"));
               $this->plant('TST', $skip_lbl);
			   $this->plant('LBL', $skip_alt_cmp);
               if (!$this->action()) return false;
               $this->plant('JMP', $start_lbl);
               $this->plant('LBL', $skip_lbl);
               $this->plant('POP', $this->NOP);
			   return true;
    		   }
			elseif (($this->keyword_is('EACH') || $this->keyword_is('ALL')) && $this->advance()) {
			   $this->plant('PUSH', array('NOP', null));
			   $this->plant('PUT', $this->NOP);
			   $stmt_lbl = array('LBL', $this->next_label());
			   while (true) {
			      $this->plant('LBL', $start_lbl);
				  $start_lbl = array('LBL', $this->next_label());
			      $this->plant('TLMT', $skip_lbl);
			      $this->plant('GET', $for_var);
				  if (!$this->expression()) {
				     $this->syntax_error($this->token,"FOR {$for_var[1]} ALL/EACH then expects an expression list");
					 return false;
				     }
			      $this->plant('PUT', $this->NOP);
				  $this->plant('PUSHA', $start_lbl);
				  $this->plant('JMP', $stmt_lbl);
			      if ($this->symbol_is(',') && $this->advance()) continue;
				  else {
				     $this->plant('JMP', $skip_lbl);
					 break;
					 }
			      }
			   $this->plant('LBL', $stmt_lbl);
			   if (!$this->action()) return false;
			   $this->plant('POPA', $this->NOP);
			   $this->plant('LBL', $start_lbl);
			   $this->plant('LBL', $skip_lbl);
			   }
			else {
			   $this->syntax_error($this->token,"FOR {$for_var[1]} then expects keyword IS, ALL or EACH");
			   return false;
			   }
			return true;
		    }
         elseif ($this->is_keyword()) {
            $np = 'AAPR';
            switch ($this->token[1]) {
               case 'ALL': break;
               case 'STRING': $np = 'ASPR'; break;
               case 'NUMBER': $np = 'ANPR'; break;
               default:
                  $this->syntax_error($this->token,"FOR statement followed by {$this->token[1]} not allowed, expected ALL, STRING or NUMBER");
                  return false;
               }
            $this->advance();
            if ($this->keyword_is('PROPERTY') || $this->keyword_is('PROPERTIES')) $this->advance();
            if ($this->keyword_is('AS')) $this->advance();
            if ($this->simple_variable()) {
               $p_name = $this->token;
               $this->advance();
               $this->plant('PUSH', $this->NULL_token);
               $this->plant('PUT', $this->NOP);
               $this->plant('LBL', $start_lbl);
               $this->plant('TLMT', $skip_lbl);
               if ($this->keyword_is('IN') && $this->advance()) {
                  if (($this->state_as_var() || $this->variable()) && $this->advance()) {
                     $this->plant('GET', $p_name);
                     $this->plant($np, $this->NOP);
                     $this->plant('TST', $skip_lbl);
                     if ($this->keyword_is('THEN')) $this->advance();
                     if ($this->keyword_is('DO')) $this->advance();
                     if (!$this->action()) return false;
                     $this->plant('JMP', $start_lbl);
                     $this->plant('LBL', $skip_lbl);
                     $this->plant('POP', $this->NOP);
                     return true;
                     }
                  else {
                     $this->syntax_error($this->token,"FOR statement expects variable after IN");
                     return false;
                     }
                  }
               else {
                  $this->syntax_error($this->token,"FOR statement expects keyword IN");
                  return false;
                  }
               }
            else {
               $this->syntax_error($this->token,"FOR statement expects next simple variable for property name assignment");
               return false;
               }
            }
         else $this->syntax_error($this->token,"FOR statement expects next keyword ALL, STRING or NUMBER, or a simple variable for STEP UNTIL");
         }
      return false;
      }
      

   private function while_block() {
      if ($this->keyword_is('WHILE') && $this->advance()) {
         if ($this->keyword_is('LIMIT') && $this->advance()) {
            if (!$this->expression()) {
               $this->syntax_error($this->token,"LIMIT clause in WHILE statement requires an expression or value");
               return false;
               }
            }
         else $this->plant('PUSH', array('NOP',null));
         $loop_label = array('LBL', $this->next_label());
         $skip_label = array('LBL', $this->next_label());
         $this->plant('LBL',$loop_label);
         $this->plant('TLMT', $skip_label);
         if ($this->expression()) {
            if ($this->keyword_is('THEN')) $this->advance();
            if ($this->keyword_is('DO')) $this->advance();
            $this->plant('TST',$skip_label); 
            if (!$this->action()) return false;
            $this->plant('JMP', $loop_label);
            $this->plant('LBL', $skip_label);
            $this->plant('POP', $this->NOP);
            return true;
            }
         $this->syntax_error($this->token, "Invalid WHILE expression");
         return false;
         }
      return false;
      }

   private function code_block() {
      if ($this->keyword_is('BEGIN') && $this->advance()) {
         while (!$this->keyword_is('END') && $this->stmt()) {}
         if ($this->keyword_is('END') && $this->advance()) {return true;}
         $this->syntax_error($this->token, "Unable to find END keyword to code block");
         return false;
         }
      return false;
      }

   private function assignment() {
      if ($this->is_name()) {
	     $assign_to = $this->token;
		 $this->advance();
		 if ($this->symbol_is('?') || $this->symbol_is('.') || $this->symbol_is('=')) {
		    if ($assign_state = $this->symbol_is('?')) $this->advance();
			if (($assign_prop = $this->symbol_is('.')) && $this->advance()) {
			   if ($this->is_name()) { $assign_prop = $this->token; $this->advance(); }
			   else $assign_prop = false;
			   }
			if ($this->symbol_is('=') && $this->advance()) {
			   if ($assign_state) {
			      $return = array('LBL', $this->next_label());
			      $this->plant('PUSHA', $return);
			      $this->plant('PUSH', array('INT',0));
				  if ($this->is_name()) {
			         $this->plant('PUSH',$this->token);
					 $this->advance();
					 }
				  elseif (!$this->expression()) $this->syntax_error($assign_to, "Invalid assignment statement to a STATE");
			      $this->plant("STAS", $assign_to);
			      $this->plant('LBL',$return);
				  $this->plant('POP', $this->NOP);
			      }
			   elseif ($this->expression()) {
			      if ($assign_prop !==false) {
				     $this->plant('PUSH', $assign_prop);
				     $this->plant("STAA",$assign_to);
					 }
			      else {
				     $this->plant('PUSH', array('NAME','.'));
				     $this->plant("STAA",$assign_to);
					 }
			      }
			   else {
			      $this->syntax_error($assign_to, "Invalid assignment statement to a STATE");
			      }
			   }
			else $this->retard();
		    }
		 else $this->retard();
		 }
      elseif ($this->variable()) {
         $assign_to = $this->token;
         $this->advance();
		 if ($this->is_auto_op()) {
		    $op = $this->token[1][0];
			$this->advance();
		    $this->plant('DUP',$this->NOP);
			$this->plant('EVAL',$this->NOP);
            if (!$this->expression()) { $this->syntax_error($assign_to, "Invalid assignment statement"); return false; }
			$this->plant('OP', array('OP',$op));
            $this->plant('PUT',$this->NOP);
            return true;
		    }
         elseif ($this->symbol_is('=') && $this->advance()) {
            if (!$this->expression()) { $this->syntax_error($assign_to, "Invalid assignment statement"); return false; }
            $this->plant('PUT',$this->NOP);
            return true;
            }
         elseif ($this->keyword_is('DEFAULT') && $this->advance()) {
            if (!$this->expression()) { $this->syntax_error($assign_to, "Invalid global DEFAULT statement"); return false; }
            $this->plant('CPUT',$this->NOP);
            return true;
            }
         else {
            $this->syntax_error($assign_to, "Invalid assignment or default statement"); 
            return false; 
            }
         }
      return false;
      }

   private function state_term() {
      if ($this->is_name()) {
	     $mc = $this->token;
	     $this->advance();
		 if ($this->is_state_op()) {
		    $this->advance();
			$return = array('LBL', $this->next_label());
			$this->plant('PUSHA', $return);
			$this->plant('PUSH', array('INT',0));
            if (!$this->term()) { $this->syntax_error($mc, "Invalid state change statement"); return false; }
            $this->plant('STAT',$mc);
			$this->plant('LBL',$return);
            return true;
		    }
		 if ($this->symbol_is('?')) {
			$this->plant('STAG', $mc);
			$this->advance();
			return true;
		    }
	     if ($this->symbol_is('.') && $this->advance()) {
            if ($this->is_var()) {
			   $this->plant('GET', $this->token);
               $this->plant('EVAL', $this->NOP);
			   $this->advance();
               }
		    elseif ($this->is_name()) {
			   $this->plant('PUSH', $this->token); 
			   $this->advance();
			   }
			else $this->plant('PUSH',array('NAME','.'));
			$this->plant('STAP', $mc);
			return true;
			}
		 else {
		    $this->retard();
			return false;
			}
	     }
	  return false;
      }	  

   private function function_term() {
      if ($this->is_name() || $this->is_var()) {
         $call_name = $this->token;
         $this->advance();
         if ($this->symbol_is('(')) {
            $return = array('LBL', $this->next_label());
            $this->plant('PUSHA', $return);
            $this->advance();
            $arg_count = $this->arg_list();
            $this->plant('PUSH', array('INT',$arg_count));
            if (!$this->symbol_is(')')) $this->syntax_error($call_name, "Invalid end to function");
            else { 
               $this->plant('CALL',$call_name);
               $this->plant('LBL',$return);
               return true; 
               }
            }
         else $this->retard();
         }
      return false;
      }

   private function function_call() {
      if ($this->function_term() && $this->advance()) { $this->plant('POP',$this->NOP); return true; }
      return false;
      }

   private function arg_list() {
      $args = 0;
      while ($this->expression()) {
         $args++;
         if (!$this->symbol_is(',')) break;
         $this->advance();
         }
      return $args;
      }
   private function a_expression() {
      return $this->multiplicant_exp() || $this->modulo_exp() || $this->additive_exp();
	  }
   private function expression() {
      if ($this->term()) {
         if ($this->boolean_exp() || $this->a_expression() || $this->conditional_exp() ) {}
         return true;
         }
      return false;
      }
   private function boolean_exp() {
      if ($this->keyword_is("AND") || $this->keyword_is("OR")) {
         $ins = ($this->keyword_is("AND"))?'AND':'OR';
         $this->advance();
         $skip_label = array('LBL', $this->next_label());
         $this->plant($ins, $skip_label);
         $this->plant('POP', $this->NOP);
         if (!$this->expression()) {$this->syntax_error($this->token, "Expected expression after compare with AND or OR "); return false; }
         $this->plant('LBL', $skip_label);
         return true;
         }
      return false;
      }

   private function var_name_term() {
      if ($this->is_name() || $this->is_var()) {
         $fn = $this->token;
         $this->advance();
         if ($this->keyword_is('EXISTS') || $this->keyword_is('NOT')) {
            $fnex = ($this->keyword_is('NOT') && $this->advance() && $this->keyword_is('EXISTS'))?'FNNEX':'FNEX';
            $this->advance();
            $this->plant($fnex, $fn);
            return true;
            }
         if ($this->keyword_is('HAS') && $this->advance()) {
		    $this->plant('GET', $fn);
            $does_have = true;
			$is_like = false;
            if ($this->keyword_is('NOT')) { $does_have = false; $this->advance(); }
            if ($this->keyword_is('PROPERTY')) $this->advance();
            if ($this->keyword_is('LIKE')) { $is_like = true; $this->advance(); }
            if ($this->term()) {
			   $this->a_expression();
               $this->plant(($does_have)?'IMP':'NIMP',array('LIKE',$is_like));
			   return true;
               }
            else {
               $this->syntax_error($this->token, "Invalid boolean expression, testing for existing PROPERTY");
               return false;
               }
            }
         $this->retard();
         if ($this->function_term()) {
            $this->advance();
            return true;
            }
		 elseif ($this->state_term()) {
			return true;
			}
	     elseif ($fn[0]=='NAME') {
            $this->syntax_error($this->token, "Misuse of function or state name, no keyword EXISTS or not correct function or set state call");
            return false;
			}
         }
      if ($this->variable()) {
         $this->advance(); 
		 if ($this->symbol_is('(')) {
		    $this->retard();
            if ($this->function_term()) {
               $this->advance();
               return true;
               }
            $this->syntax_error($this->token, "Use of indirect function call, badly formed function call");
            return false;
		    }
         if ($this->keyword_is('HAS') && $this->advance()) {
            $does_have = true;
			$is_like = false;
            if ($this->keyword_is('NOT')) { $does_have = false; $this->advance(); }
            if ($this->keyword_is('PROPERTY')) $this->advance();
            if ($this->keyword_is('LIKE')) { $is_like = true; $this->advance(); }
            if ($this->term()) {
			   $this->a_expression();
			   $this->plant(($does_have)?'IMP':'NIMP',array('LIKE',$is_like));
			   return true;
               }
            else {
               $this->syntax_error($this->token, "Invalid boolean expression, testing for existing PROPERTY");
               return false;
               }
            }
         else {
            $this->plant('EVAL',$this->NOP);
            }
         return true; 
         }
      elseif ($this->bool_const()) return true;
      return false; 
      }

   private function conditional_exp() {
      if (($this->keyword_is('IS')&&$this->advance()) || (!is_null($condition = $this->is_condition()))) {
         $equals = true;
         if ($this->keyword_is('NOT')) { $equals = false; $this->advance(); }
         if (!is_null($condition = $this->is_condition())) $this->advance();
         if ($this->term()) {
		    $this->a_expression();
            $this->plant(($equals)?'CMP':'NCMP',(is_null($condition))?$this->EQ:$condition);
			$this->boolean_exp();
            }
         else {
            $this->syntax_error($this->token, "Invalid boolean expression, invalid expression to compare");
            return false;
            }
         return true;
		 }
	  return false;
      }

   private function bool_const() {
      if ($this->keyword_is('TRUE') && $this->advance()) {
         $this->plant('PUSH', array('TRUE', true));
         return true;
         }
      elseif ($this->keyword_is('FALSE') && $this->advance()) {
         $this->plant('PUSH', array('FALSE', false));
         return true;
         }
      else return false;
      }

   private function modulo_exp() {
      if ($this->is_modulo_op()) {
         $op = $this->token;
         $this->advance();
         if ($this->term()) {
            $this->plant('OP',$op);
			$this->a_expression() || $this->conditional_exp() || $this->boolean_exp();
            return true;
            }
         else { 
            $this->syntax_error($op, "Invalid Expression");
            return false;
            }
         }
      return false;
      }


   private function multiplicant_exp() {
      if ($this->is_multiplicant_op()) {
         $op = $this->token;
         $this->advance();
         if ($this->term()) {
            $this->plant('OP',$op);
			$this->a_expression() || $this->conditional_exp() || $this->boolean_exp();
            return true;
            }
         else { 
            $this->syntax_error($op, "Invalid Expression");
            return false;
            }
         }
      return false;
      }

   private function additive_exp() {
      if ($this->is_additive_op()) {
         $op = $this->token;
         $this->advance();
         if ($this->term()) {
		    $this->a_expression();
            $this->plant('OP', $op);
			$this->conditional_exp() || $this->boolean_exp();
            return true;
            }
         else {
            $this->syntax_error($op, "Invalid Expression");
            return false;
            }
         }
      return false;
      }

   private function bracket_expression() {
      if ($this->symbol_is('(') && $this->advance()) {
         $this->expression();
         if (!$this->symbol_is(')')) {
            $this->syntax_error($this->token, "Invalid Expression in Brackets");
            return false;
            }
         return true;
         }
      return false;
      }

   private function term() {
      if ($this->is_additive_op()) {
	     $neg = $this->symbol_is('-');
		 if ($this->advance() && $this->term()) {
		    if ($neg) $this->plant('NEG', $this->NOP);
			return true;
			}
		 }
      elseif ($this->keyword_is('NOT') && $this->advance()) {
	     if ($this->term()) {
		    $this->plant('NOT', $this->NOP);
			return true;
			}
		 else {$this->syntax_error($this->token, "Expected boolean expression after NOT "); return false; }
		 }
      elseif ($this->var_name_term()) {
         return true;
         }
	  elseif ($this->bool_const()) {
         return true;
         }		 
      elseif ($this->is_num()) {
	     $num_token = $this->token;
         if ($this->advance() && $this->symbol_is('.') && $this->advance() && $this->is_num()) {
		    $num_token[1] .= '.';
			$num_token[1] .= $this->token[1];
			$this->advance();
			}
         $this->plant('PUSH', $num_token);
         return true;
         }
      elseif ($this->is_null_const()) {
         $this->plant('PUSH', array('NULL', NULL));
         return $this->advance();
         }
      elseif ($this->is_empty_const()) {
         $this->plant('PUSH', array('EMPTY', NULL));
         return $this->advance();
         }
      elseif ($this->string_expression() && $this->advance()) {
         return true;
         }
      elseif ($this->is_string()) {
         $this->plant('PUSH', $this->token);
         return $this->advance();
         }
      elseif ($this->bracket_expression()) {
         return $this->advance();
         }
      elseif ($this->literal_term()) {
         return true;
         }
      return false;
      }

   private function literal_term() {
      if ($this->keyword_is('LITERAL') && $this->advance()) {
         if ($this->expression()) {
            $this->plant('LITP', $this->NOP);
            return true;
            }
         elseif ($this->is_literal()) {
            $this->plant('PUSH', $this->token);
            $this->plant('LITP', $this->NOP);
            $this->advance();
            return true;
            }
         else $this->syntax_error($this->token, "LITERAL expects a string in {...} or a string expression");
         }
      return false;
      }

   private function string_expression() {
      if ($this->is_string()) {
         $s = $this->token[1];
         $this->mark();
         $in_exp = 0;
         $s_build = "";
         $e_build = "";
         $is_complex_string = false;
         $this->plant('PUSH', array('STRING', ""));
         for ($i=0; $i<strlen($s); $i++) {
            if ($s[$i]=='{') {
               if ($in_exp==0) {
                  $this->plant('PUSH', array('STRING', $s_build));
                  $s_build = "";
                  $this->plant('OP', array('OP','+'));
                  }
               else $e_build .= '{';
               $in_exp++;
               }
            elseif ($s[$i]=='}' && $in_exp>0) {
               if ($in_exp==1 && strlen($e_build)>0) {
			      try {
                     $sparser = new _parse_code($e_build);
                     $code = $sparser->compile_string($this);
					 }
				  catch (HDA_ICode_Exception $e) {
				     $code = null;
				     }
				  catch (Exception $e) {
				     $code = null;
				     }
                  if (!is_null($code)) {
                     foreach($code as $op) $this->plant($op[0], $op[1]);
                     $this->plant('OP', array('OP','+'));
                     $e_build = "";
                     $is_complex_string = true;
                     }
                  else  {
                     $s_build .= "{{$e_build}}";
                     $e_build = "";
                     }
                  }
               else $e_build .= "}";
               $in_exp--;
               }
            else {
               if ($in_exp>0) $e_build .= $s[$i]; else $s_build .= $s[$i];
               }
            }
         if ($is_complex_string) {
            $flush = $s_build;
            if (strlen($e_build)>0) $flush .= "{{$e_build}";
            elseif ($in_exp>0) $flush .= "}";
            $this->plant('PUSH', array('STRING', "{$flush}"));
            $this->plant('OP', array('OP','+'));
            $this->plant_mark();
            return true;
            }
         $this->unmark();
         }
      return false;
      }

   private $var_builder="";
   private function state_as_var() {
      if ($this->is_name()) {
         $this->var_builder = $this->token[1];
	     $this->plant('GET',$this->token);
		 return true;
		 }
	  return false;
      }
   private function variable() {
      if ($this->is_var()) {
         $this->var_builder = $this->token[1];
         $this->plant('GET', $this->token);
         $this->properties();
         return true;
         }
      return false;
      }

   private function simple_variable() {
      if ($this->is_var()) {
         $this->plant('GET', $this->token);
         return true;
         }
      return false;
      }

   private function properties() {
      while ($this->property()) {}
      }

   private function property() {
      if ($this->advance() && $this->symbol_is('.')) {
         $this->advance();
         $this->var_builder .= ".{$this->token[1]}";
         if ($this->is_var()) {
            $this->plant('GET', $this->token);
            $this->plant('EVAL', $this->NOP);
            $this->plant('PROP', $this->NOP);
            return true;
            }
         elseif ($this->is_num()) {
            $this->plant('PUSH', $this->token);
            $this->plant('PROP', $this->NOP);
            return true;
            }
         elseif ($this->is_name()) {
            $this->plant('PUSH', $this->token);
            $this->plant('PROP', $this->NOP);
            return true;
            }
         elseif ($this->string_expression()) {
            $this->plant('PROP', $this->NOP);
            }
         elseif ($this->is_string()) {
            $this->plant('PUSH', $this->token);
            $this->plant('PROP', $this->NOP);
            }
	     elseif ($this->symbol_is('.')) {
		    $this->plant('CPROP', $this->NOP);
			}
         else {
            $this->syntax_error($this->token, "Invalid property modifier");
            $this->retard();
            return false;
            }
         }
      else {
         $this->retard();
         return false;
         }
      }
         

   private function is_keyword() {
      return ($this->token[0]=='KEY');
      }
   private function keyword_is($k) {
      return ($this->is_keyword() && $this->token[1]==$k);
      }
   private function is_symbol() {
      return ($this->token[0]=='SYM');
      }
   private function symbol_is($y) {
      return ($this->is_symbol() && $this->token[1]==$y);
      }
   private function is_multiplicant_op() {
      return ($this->token[0]=='SYM' &&  ($this->token[1]=='*' || $this->token[1]=='/'));
      }
   private function is_modulo_op() {
      return ($this->token[0]=='SYM' &&  ($this->token[1]=='%'));
      }
   private function is_additive_op() {
      return ($this->token[0]=='SYM' && ($this->token[1]=='+' || $this->token[1]=='-'));
      }
   private function is_auto_op() {
      return ($this->is_symbol() && ($this->token[1]=='+=' || $this->token[1]=='-='));
      }
   private function is_state_op() {
      return ($this->is_symbol() && ($this->token[1]=='->'));
      }
   
   private function is_condition() {
      if ($this->is_keyword()) switch($this->token[1]) {
        case 'LESS_THAN':
        case 'LT': return array('COND','LT');
        case 'LESS_THAN_OR_EQUAL':
        case 'LE': return array('COND','LE');
        case 'GREATER_THAN':
        case 'GT': return array('COND','GT');
        case 'GREATER_THAN_OR_EQUAL':
        case 'GE': return array('COND','GE');
		case 'LIKE': return array('COND','LIKE');
        }
      elseif ($this->is_symbol()) switch($this->token[1]) {
        case '<': return array('COND','LT');
        case '<=':
        case '=<': return array('COND','LE');
        case '>': return array('COND','GT');
        case '=>':
        case '>=': return array('COND','GE');
        }
      return null;
      }
   private function is_var() {
      return ($this->is_local_var() || $this->is_global_var());
      }
   private function is_local_var() {
      return ($this->token[0]=='VAR');
      }
   private function is_global_var() {
      return ($this->token[0]=='GVAR');
      }
   private function is_literal() {
      return ($this->token[0]=='LIT');
      }
   private function is_num() {
      return ($this->token[0]=='INT');
      }
   private function is_null_const() {
      return $this->keyword_is('NULL');
      }
   private function is_empty_const() {
      return $this->keyword_is('EMPTY');
	  }
   private function is_string() {
      return ($this->token[0]=='STRING');
      }
   private function is_name() {
      return ($this->token[0]=='NAME');
      }
   private function is_eof() {
      return ($this->token[0]=='EOF');
      }
   private function is_nopstmt() {
      return ($this->token[0]=='NOPSTMT');
      }

   private function plant($icode, $t) {
      $this->execute[] = array($icode, $t);
      }
   private function mark() {
      $this->execute[] = array('NOP',$this->NOP);
      $this->marks[] = count($this->execute);
      }
   private function unmark() {
      if (count($this->marks)>0) {
         $p = array_pop($this->marks);
         array_splice($this->execute, $p);
         }
      }
   private function plant_mark() {
      if (count($this->marks)>0) array_pop($this->marks);
      }
      
// End Syntax


	  
   private function syntax_error($tok, $e) {
      $s = "";
      $from = max($this->on_tok-10, 0);
      $to = min($from+14, count($this->toks));
      for ($i=$from; $i<$to; $i++) $s .= "{$this->toks[$i][1]} ";
      $this->error[] = "Syntax error near ..\"{$s}\".. {$e}";
	  return false;
      }
	  
   public function errors() {
      return (count($this->error)>0)?$this->error:null;
	  }
	  
   public function hdocs() {
      return $this->_tokenizer->hdocs;
	  }
	  
   public function metatags() {
      return $this->_tokenizer->metatags;
	  }
	  

	  
   public function dump_tok() {
      $s = "";
      foreach ($this->toks as $a_tok) $s .= "{$a_tok[0]}: {$a_tok[1]}\n";
	  return $s;
      }
  
}

class _vm_code {
   public $trace_calls = false;
   public $no_loop_limit = true;
   public $monitor_mode = false;
   private $debug_s = "";
   private $trace_s = "";
   public $path = "";
   public $ref = null;
   public $profile = null;
   public $task = null;
   public $q = null;
   public $lib=null;
   public $_exit_code = null;

   public function ref_title() {
      return (!is_null($this->process))?$this->process['Title']:"Not Known";
      }

   private $ns = array();
   private $stns = array();
   private $fns = array();
   private $statmac = null;
   private $stack = array();
   private $rstate = false;
   private $iptr = 0;
   
   public function __destruct() {
	  unset($this->execute);
	  $this->execute = null;
	  unset($this->fns);
	  $this->fns = null;
	  unset($this->stack);
	  $this->stack = null;
	  unset($this->ns);
	  $this->ns = null;
	  unset($this->stns);
	  $this->stns = null;
	  unset($this->statmac);
	  $this->statmac = null;
	  global $glob_mem;
	  $glob_mem .= "destruct vm ".memory_get_usage(true)."\n";
      }
   public function _close_lib() {
      $this->lib->close_library_resources();
      unset($this->lib);
	  $this->lib = null;
      }
   public function __construct($execute) {
	  global $glob_mem;
	  $glob_mem .= "construct vm ".memory_get_usage(true)."\n";
      $this->execute = $execute['icode'];
	  $this->fns = $execute['fns'];
	  }
	  
   public function run(&$process, &$params_list, $with_debug=false, $with_monitor=false, $break_table=null) {
      $this->ref = $process['ProfileItem'];
	  $this->task = $process['ItemId'];
      $this->process = $process;
	  $this->q = (array_key_exists('QueueLevel',$process))?$process['QueueLevel']:0;
	  if (is_null($this->q)) $this->q = 0;
	  $this->monitor_mode = $with_monitor;
      $this->break_table = $break_table;
      $this->open_execute($params_list);
      $this->run_execute($with_debug, $with_monitor);
      $this->close_execute($params_list);
	  }
	  
   private function open_execute(&$params_list) {
      $this->ns = array();
	  $this->ns['$_scope'] = (is_array($this->process)&&array_key_exists('Title',$this->process))?$this->process['Title']:'Unknown';
      $this->stns[0] = &$this->ns;
      $this->stack = array();
      $this->rstate = false;
      $this->iptr = 0;
      $this->run_linker();
      if (!is_null($params_list) && is_array($params_list)) foreach ($params_list as $k=>$p) {
         $to = &$this->ns;
         $to[$k] = $p;
         }
      }
public $_last_vm_error = null;
public function _vm_error_handler($errno, $errmsg) {
   throw new HDA_ICode_Exception($this->_last_vm_error = "Exception on line {$this->on_debug_line}: ".error_get_last()." {$errmsg} ");
   return true;
   }
   private $be_nice = 1;
   private function run_execute($with_debug, $with_monitor, $continue=false, $step=0) {
      if ($continue===false) $this->lib = new HDA_library($this,$with_debug);
	  set_error_handler(array($this,'_vm_error_handler'));
      $this->trace_calls = $with_debug;
	  $this->debug_step = $step;
	  $nice = 10000;
	  $this->be_nice = ($this->q>0)?$this->q:1;
	  $this->be_nice = ($with_monitor)?1:$this->be_nice;
	  set_time_limit(0);
	  $instr_count = 0;
	  $instr_count_flush = 1000;
	  ignore_user_abort(false);
	  $start_mem = memory_get_usage(true);
      try {
         while ($this->icode()) { 

		    if (($instr_count % $instr_count_flush)==0) { 
			   $instr_count_flush = 1000000;
			   $abort = hda_db::hdadb()->HDA_DB_monitor($this->on_debug_line, $instr_count/1000);
			   switch ($abort) {
			      case 'ABORTED':
			         throw new HDA_ICode_Exception("Aborted after {$instr_count} instructions at line {$this->on_debug_line}");
					 break;
				  }
			   }
			$instr_count++;
			}
		 $end_mem = memory_get_usage(true);
		 $use_mem = $end_mem - $start_mem;
		 $this->debug_s .= "\nMemory {$start_mem} {$end_mem} {$use_mem}\n";
         }
      catch (HDA_ICode_Exception $e) {
		  HDA_LogThis("ICODE Exception {$this->ref} task {$this->task} ".$e->getMessage(), 'COMPILER');
         $msg_e = "HDAW Icode Exception ".$e->getMessage()." ";
		 $msg_e .= print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true);
		 $msg_e .= " {$this->_last_vm_error} ";
         $op = $this->execute[$this->iptr][0];
         $r = $this->execute[$this->iptr][1][1];
         $rt = $this->execute[$this->iptr][1][0];
		 $msg_e .= " {$op} {$r} {$rt} ";
         $this->debug_s .= $msg_e;
		 $this->_exit_code = $msg_e;
         }
      catch (Exception $e) {
		  HDA_LogThis("Exception {$this->ref} task {$this->task} ".$e->getMessage(), 'COMPILER');
         $msg_e = "Exception ".$e->getMessage()." ";
		 $msg_e .= print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true);
         $this->debug_s .= $msg_e;
		 $this->_exit_code = $msg_e;
         }
      }
   

   private function close_execute(&$params_list) {
      if (!is_null($params_list) && is_array($params_list)) foreach ($params_list as $k=>$p) {
         $to = &$this->ns;
         $params_list[$k] = $to[$k];
         }

	  restore_error_handler();
      }

   private function run_linker() {
      $link_table = array();
      for ($iptr = 0; $iptr<count($this->execute); $iptr++) {
         $op = $this->execute[$iptr][0];
         switch ($op) {
            case 'LBL': $link_table[$this->execute[$iptr][1][1]]=$iptr;
            }
         }
      for ($iptr = 0; $iptr<count($this->execute); $iptr++) {
         $op = $this->execute[$iptr][0];
         switch ($op) {
            case 'TST': 
            case 'AND':
            case 'OR':
            case 'TLMT':
            case 'JMP':
            case 'PUSHA':
               $this->execute[$iptr][1][1] = $link_table[$this->execute[$iptr][1][1]];
               break;
            }
         }
      foreach ($this->fns as $k=>$p) {
         $this->fns[$k] = $link_table[$p[1]];
         }
         
      for ($iptr = 0; $iptr<count($this->execute); $iptr++) {
         $op = $this->execute[$iptr][0];
         $r = $this->execute[$iptr][1][1];
		 if (is_object($r)) $r = "Empty";
         $this->debug_s .= sprintf("%04d", $iptr).": ".sprintf("% -4s",$op)." {$r}\n";
         }
      }
   private $_last_iptr = -1;
   private function icode() {
      if ($this->iptr>=count($this->execute)) return false;
	  if ($this->iptr==$this->_last_iptr) throw new HDA_ICode_Exception("VM error at {$this->iptr}");
      $this->_last_iptr = $this->iptr;	  
      $op = $this->execute[$this->iptr][0];
      $r = $this->execute[$this->iptr][1][1];
      $rt = $this->execute[$this->iptr][1][0];
      switch ($op) {
         case 'GET': 
		    switch ($rt) {
			   case 'VAR': 
			      $n = array(&$this->stns[count($this->stns)-1]); 
                  if (!array_key_exists($r, $n[0])) $n[0][$r] = null;
			      break;
			   case 'NAME': 
			      $n = array(&$this->stns[0]); 
				  $n[0][$r] = $r;
				  break;
			   case 'GVAR': 
			      $n = array(&$this->stns[0]); 
                  if (!array_key_exists($r, $n[0])) $n[0][$r] = null;
			      break;
			   }
            $this->stack[] = array(&$n[0][$r]);
            break;
         case 'EVAL':
            $to = array_pop($this->stack);
            $this->stack[] = (is_array($to[0]))?$to[0]:$to[0];
            break;
         case 'PUSHA':
            $this->stack[] = $r;
            $this->stack[] = "__STMK__";
            break;
		 case 'POPA':
		    array_pop($this->stack); // STMK
			$this->iptr = array_pop($this->stack);
			break;
         case 'PUSH':
            $this->stack[] = $r;
            break;
         case 'POP':
            array_pop($this->stack);
            break;
         case 'STNS':
            $s = array();
			$s['$_scope'] = $r;
            $this->stns[] = &$s;
            break;
         case 'PUT':
            $v = array_pop($this->stack);
            $to = array_pop($this->stack);
            $to[0] = $v;
            break;
         case 'CPUT':
            $v = array_pop($this->stack);
            $to = array_pop($this->stack);
            if (is_null($to[0]) || (is_object($to[0]) && $to[0]=='EMPTY')) $to[0] = $v;
            break;
         case 'PROP':
            $r = array_pop($this->stack);
            $to = array_pop($this->stack);
            if (!is_array($to[0])) $to[0] = array();//
            if (!array_key_exists($r, $to[0])) $to[0][$r] = null; 
            $this->stack[] = array(&$to[0][$r]);
            break;
		 case 'CPROP':
            $to = array_pop($this->stack);
            if (!is_array($to[0])) $to[0] = array();
			$r = array_keys($to[0]);
			sort($r);
			$i = count($r)-1;
			$i = (array_key_exists($i, $r) && is_numeric($r[$i]))?($r[$i]+1):($i+1);
            $to[0][$i] = null;
            $this->stack[] = array(&$to[0][$i]);
            break;		 
         case 'USET':
            $to = array_pop($this->stack);
            $p = array_pop($this->stack);
            switch($r) {
               default:
               case 'P': 
                  if (is_array($to[0])) {
                     if (array_key_exists($p, $to[0])) unset($to[0][$p]); 
                     }
                  break;
               case 'A':
                  if (is_array($to[0])) {
                     $ks = array_keys($to[0]);
                     foreach ($ks as $k) unset($to[0][$k]);
                     }
                  break;
               case 'N':
                  if (is_array($to[0])) {
                     $ks = array_keys($to[0]);
                     foreach ($ks as $k) if (is_numeric($k)) unset($to[0][$k]);
                     }
                  break;
               case 'S':
                  if (is_array($to[0])) {
                     $ks = array_keys($to[0]);
                     foreach ($ks as $k) if (!is_numeric($k)) unset($to[0][$k]);
                     }
                  break;
               }
            break;
         case 'OP':
            $v1 = array_pop($this->stack);
            $v2 = array_pop($this->stack);
            $this->stack[] = $this->_do_op($r, $v1, $v2);
            break;
         case 'TST':
            $result = array_pop($this->stack);
            if ($result===false) $this->iptr = $r;
            break;
         case 'TLMT':
            if (!$this->no_loop_limit) {
               $count = array_pop($this->stack);
			   if (!is_null($count)) {
                  $count--;
                  $this->stack[] = $count;
                  if ($count<0) $this->iptr = $r; 
				  }
			   else $this->stack[] = null;
               }
            break;
         case 'AND':
            $result = array_pop($this->stack);
            if ($result===false) $this->iptr = $r;
            $this->stack[] = $result;
            break;
         case 'OR':
            $result = array_pop($this->stack);
            if ($result===true) $this->iptr = $r;
            $this->stack[] = $result;
            break;
		 case 'NEG':
		    $result = array_pop($this->stack);
			$this->stack[] = -$result;
			break;
		 case 'NOT':
		    $result = array_pop($this->stack);
			$this->stack[] = !$result;
			break;
		 case 'INC':
		    $result = array_pop($this->stack);
			$this->stack[] = $result+1;
			break;
         case 'JMP':
            $this->iptr = $r;
            break;
         case 'RTN':
            array_pop($this->stns);
            $v1 = array_pop($this->stack);
            $v2 = "Not STMK";
            while (strcmp($v2, "__STMK__")<>0) $v2 = array_pop($this->stack);
            $this->iptr = array_pop($this->stack);
            $this->stack[] = $v1;
            break;
         case 'SWP':
            $v1 = array_pop($this->stack);
            $v2 = array_pop($this->stack);
            $this->stack[] = $v1;
            $this->stack[] = $v2;
            break;
	     case 'DUP':
		    $v1 = array_pop($this->stack);
			$this->stack[] = $v1;
			$this->stack[] = $v1;
			break;
		 case 'CASE':
            $v1 = array_pop($this->stack);
            $v2 = array_pop($this->stack);
			$this->stack[] = $v2;
            $this->stack[] = !$this->_do_cmp($v2, $v1, $r);
			break;
         case 'CMP':
            $v1 = array_pop($this->stack);
            $v2 = array_pop($this->stack);
            $this->stack[] = $this->_do_cmp($v2, $v1, $r);
            break;
         case 'NCMP':
            $v1 = array_pop($this->stack);
            $v2 = array_pop($this->stack);
            $this->stack[] = !$this->_do_cmp($v2, $v1, $r);
            break;
         case 'IMP':
            $v1 = array_pop($this->stack);
            $v2 = array_pop($this->stack);
            $this->stack[] = $this->_do_imp($v1, $v2, $r);
            break;
         case 'NIMP':
            $v1 = array_pop($this->stack);
            $v2 = array_pop($this->stack);
            $this->stack[] = !$this->_do_imp($v1, $v2, $r);
            break;
         case 'FNEX':
            $this->stack[] = $this->_can_resolve($r, $rt);
            break;
         case 'FNNEX':
            $this->stack[] = !$this->_can_resolve($r, $rt);
            break;
         case 'AAPR':
         case 'ASPR':
         case 'ANPR':
            $v1 = array_pop($this->stack);
            $v2 = array_pop($this->stack);
            $this->stack[] = $this->_do_nprop($v1, $v2, $op);
            break;
         case 'CALL':
            if ($this->_can_resolve($r, $rt)) {
               $this->iptr = $this->fns[$r];
               }
            else {
               $arg_count = array_pop($this->stack);
               $params = array();
               while ($arg_count>0) {
                  array_unshift($params,array_pop($this->stack));
                  $arg_count--;
                  }
               array_pop($this->stack); // stk mark
               array_pop($this->stack); // return address
               $this->stack[] = $this->_do_call($r, $params);
			   unset($params);
               }
            break;
         case 'LIT':
            $path = array_pop($this->stack);
            $v1 = array_pop($this->stack);
            $this->_do_literal($v1, $path);
            break;
         case 'LITP':
            $v1 = array_pop($this->stack);
            $this->stack[] = $this->_do_literal($v1, null);
            break;
		 case 'STAT':
            $v = array_pop($this->stack);
			$v = $this->_do_stat($v, $r);
			if ($v===true && ($r=$this->_can_resolve_state($r))) {
               $this->iptr = $this->fns[$r];
               }
            else {
               array_pop($this->stack); // stk mark
               array_pop($this->stack); // return address
			   $this->stack[] = $v;
			   }
			break;
		 case 'DSTT':
		 case 'SSTT':
		    $sfrom = array_pop($this->stack);
			$sto = array_pop($this->stack);
			$sback = array_pop($this->stack);
			$v = array_pop($this->stack);
			$this->_do_stat_set($r, $sfrom, $sto, $sback, $v, $op=='SSTT');
			break;
		 case 'STAG':
			$this->stack[] = $this->_do_stat_state($r);
			break;		 
		 case 'STAP':
		    $v = array_pop($this->stack);
			$this->stack[] = $this->_do_stat_prop($r, $v);
			break;
		 case 'STAS':
		    $v = array_pop($this->stack);
			$v = $this->_do_stat_assign($r, $v, '?');
			if ($v===true && ($r=$this->_can_resolve_state($r))) {
               $this->iptr = $this->fns[$r];
               }
            else {
               array_pop($this->stack); // stk mark
               array_pop($this->stack); // return address
			   $this->stack[] = $v;
               }
			break;
		 case 'STAA':
		    $prop = array_pop($this->stack);
			$v = array_pop($this->stack);
			$this->_do_stat_assign($r, $v, $prop);
			break;
         case 'STAE':
            $v = array_pop($this->stack);
            $this->_do_stat_extends($r, $v);
            break;			
         case 'EXIT': 
            $this->_exit_code = array_pop($this->stack);
            $this->_do_exit(); 
            return false;
         case 'EXCP': 
            $v1 = array_pop($this->stack);
            $this->_do_exception($r, $v1);
            break;
		 case 'DBG':
			$this->on_debug_line = $r;
			break;
         }
      $this->iptr++;
      return true;
      }

   private function _do_op($op, $v1, $v2) {
      switch ($op) {
         case '+': 
            if (is_numeric($v1) && is_numeric($v2)) {
			   if (_is_float_val($v1) || _is_float_val($v2)) {
			      return strval(floatval($v1)+floatval($v2));
				  }
			   return ($v1+$v2);
			   }
            return strval($v2).strval($v1);
         case '-': 
            if (is_numeric($v1) && is_numeric($v2)) {
			   if (_is_float_val($v1) || _is_float_val($v2)) {
			      return strval(floatval($v2)-floatval($v1));
				  }
			   return ($v2-$v1);
			   }
            return str_replace(strval($v1), "", strval($v2));
         case '*': 
		    if (is_numeric($v1) && is_numeric($v2)) {
			   if (_is_float_val($v1) || _is_float_val($v2)) {
			      return strval(floatval($v1)*floatval($v2));
				  }
		       return ($v1 * $v2);
			   }
            return "";
         case '/': 
		    if (is_numeric($v1) && is_numeric($v2)) {
			   if (_is_float_val($v1) || _is_float_val($v2)) {
			      return strval(floatval($v2)/floatval($v1));
				  }
			   return floor($v2/$v1);
			   }
            return "";
         case '%': 
		    if (is_numeric($v1) && is_numeric($v2)) {
			   if (_is_float_val($v1) || _is_float_val($v2)) {
			      return strval(fmod(floatval($v2),floatval($v1)));
				  }
			   return ($v2 % $v1);
			   }
            return "";
         default: return 0;
         }
      }

   private function _do_cmp($v1, $v2, $r) {
      switch ($r) {
         default:
         case 'EQ':
			if (is_null($v1) && is_null($v2)) return true;
            if (is_numeric($v1) && is_numeric($v2)) {
			   if (_is_float_val($v1) || _is_float_val($v2)) {
			      return (floatval($v1)==floatval($v2));
				  }
			   return ($v1==$v2);
			   }
            if (is_bool($v2)===true) return ($v1===$v2);
            if (is_string($v1) && is_string($v2)) {
			   $pattern = null;
			   $v = null;
			   if (strlen($v1)>0 && $v1[0]=='/') { $pattern = $v1; $v = $v2; }
			   elseif (strlen($v2)>0 && $v2[0]=='/') { $pattern = $v2; $v = $v1; }
			   if (!is_null($pattern)) return preg_match($pattern, $v);
  			   return (strcmp(strval($v1), strval($v2))==0);
			   }
			return false;
         case 'LT':
            if (is_numeric($v1) && is_numeric($v2)) {
			   if (_is_float_val($v1) || _is_float_val($v2)) {
			      return (floatval($v1)<floatval($v2));
				  }
			   return ($v1<$v2);
			   }
            else return (strcmp(strval($v1), strval($v2))<0);
         case 'LE':
            if (is_numeric($v1) && is_numeric($v2)) {
			   if (_is_float_val($v1) || _is_float_val($v2)) {
			      return (floatval($v1)<=floatval($v2));
				  }
			   return ($v1<=$v2);
			   }
            else return (strcmp(strval($v1), strval($v2))<=0);
         case 'GT':
            if (is_numeric($v1) && is_numeric($v2)) {
			   if (_is_float_val($v1) || _is_float_val($v2)) {
			      return (floatval($v1)>floatval($v2));
				  }
			   return ($v1>$v2);
			   }
            else return (strcmp(strval($v1), strval($v2))>0);
         case 'GE':
            if (is_numeric($v1) && is_numeric($v2)) {
			   if (_is_float_val($v1) || _is_float_val($v2)) {
			      return (floatval($v1)>=floatval($v2));
				  }
			   return ($v1>=$v2);
			   }
            else return (strcmp(strval($v1), strval($v2))>=0);
		 case 'LIKE':
			return (@preg_match("/{$v2}/i",$v1)==1);
         }
      }

   private function _do_imp($v1, $v2, $is_like) {
      if (!is_array($v2)) return false;
      if (!is_array($v2[0])) {
	     if ($this->_do_is_stat($v2[0])) $v2 = array($this->_do_stat_values($v2[0]));
		 else return false;
		 }
      if (!is_array($v2[0])) return false;
      if (is_numeric($v1)) return array_key_exists($v1,$v2[0]);
	  if ($is_like==1) {
		  $keys = array_keys($v2[0]);
		  $needle = strval($v1);
		  foreach($keys as $key) if (strcasecmp($needle, $key)==0) return true;
		  return false;
	  }
      return array_key_exists(strval($v1), $v2[0]);
      }

   private function _do_nprop(&$prop, &$target, $op) {
      if (!is_array($target[0])) {
	     if ($this->_do_is_stat($target[0])) $target = array($this->_do_stat_values($target[0]));
		 else return false;
		 }
      $keys = array_keys($target[0]);
      if (!is_array($keys) || count($keys)==0) return false;
      if (!is_array($prop)) return false;
      $v = $prop[0];
      $on_key = -1;
      if (!is_null($v)) for ($i = 0; $i<count($keys); $i++) {
         if (strcmp(strval($v), strval($keys[$i]))==0) {$on_key=$i; break; }
         }
      $on_key++;
      for ($on_key = $on_key; $on_key<count($keys); $on_key++) {
         switch ($op) {
            case 'AAPR': break 2;
            case 'ASPR': if (!is_numeric($keys[$on_key])) break 2; else break;
            case 'ANPR': if (is_numeric($keys[$on_key])) break 2; else break;
            }
         }
      if ($on_key < count($keys)) {
         $to = array(&$prop[0]);
         $to[0] = $keys[$on_key];
         return true;
         }
      return false;
      }

   private function _do_literal($s, $path) {
      if (!is_string($s)) return "";
      $s = preg_replace_callback("/(?P<var>[\$]{1,1}[\w_]*)/", "_vm_code::literal_replace", $s);
      $s = preg_replace_callback("/(?P<gvar>[#]{1,1}[\w_]*)/", "_vm_code::literal_replace", $s);
      $s = str_replace('|',"\r\n", trim($s,"{}"));
	  $s = str_replace('£','|',$s);
      if (!is_null($path)) { @file_put_contents($fpath = $this->lib->_resolve_working_dir($this)."/{$path}", $s); _chmod($fpath); }
      return $s;
      }
   private function literal_replace($matches) {
      if (array_key_exists('var', $matches)) {
         $var = $matches['var'];
         $n = array(&$this->stns[count($this->stns)-1]);
         if (!array_key_exists($var, $n[0])) return null;
         return $n[0][$var];
         }
      elseif (array_key_exists('gvar', $matches)) {
         $var = $matches['gvar'];
         $n = array(&$this->stns[0]);
         if (!array_key_exists($var, $n[0])) return null;
         return $n[0][$var];
         }
      return "** blah **";
      }

   private function _can_resolve(&$r, $rt=null) {
      if ($rt=='VAR' || $rt=='GVAR') {
         $n = ($rt=='VAR')?array(&$this->stns[count($this->stns)-1]):array(&$this->stns[0]);
         if (!array_key_exists($r, $n[0])) $n[0][$r] = null;
         $to = array(&$n[0][$r]);
         $r = (is_array($to[0]))?$to[0]:$to[0];
		 }
      return array_key_exists($r, $this->fns);
      }

   private function _do_call($fn, $p) {
      $ufn = strtoupper($fn);
      $ufn = "_do_call_{$ufn}";
      if (method_exists($this->lib, $ufn)) {
         try {
            return call_user_func(array($this->lib,$ufn), $p, $this);
            }
         catch (Exception $e) {
            $err = "Run Time Library exception {$fn}: {$e} on line {$this->on_debug_line}";
            $this->error[] = $err;
            $this->debug_s .= "{$err}\n";
            throw new HDA_ICode_Exception($err);
            }
         }
      else {
         $err = "Call to missing function {$fn} on line {$this->on_debug_line}";
         $this->error[] = $err;
         $this->debug_s .= "{$err}\n";
		 throw new HDA_ICode_Exception($err);
         }

      return false;
      }

   private function _can_resolve_state($r) {
      if (!is_null($this->statmac) && $this->statmac->state_exists($r)) {
	     return ($this->_can_resolve($r) || $this->_can_resolve_state($r=$this->statmac->state_as($r)))?$r:false;
	     }
	  return ($this->_can_resolve($r))?$r:false;
	  }
   private function _do_is_stat($r) {
      return (!is_null($this->statmac) && $this->statmac->state_exists($r));
	  }
   private function _do_stat($v,$r) {
      if (is_null($this->statmac)) throw new HDA_ICode_Exception("State Function {$r} has not been defined");
	  if (is_null($r)) return false;
	  return ($this->statmac->state_change($r, $v) || $this->_do_stat($v, $this->statmac->state_as($r)));
	  }
   private function _do_stat_set($r, $sfrom, $sto, $sback, $v, $skips = false) {
      if (is_null($this->statmac)) $this->statmac = new _state_mc();
	  $this->statmac->def_state($r, $sfrom, $sto, $sback, $v, $skips);
      }
   private function _do_stat_prop($r, $prop) {
      if (is_null($this->statmac)) throw new HDA_ICode_Exception("State Function {$r} has not been defined");
	  if (is_null($r)) return null;
	  $stat = $this->statmac->state_property($r, $prop);
      if (($stat===false) || is_null($stat)) $stat = $this->_do_stat_prop($this->statmac->state_as($r), $prop);
	  return $stat;
	  }
   private function _do_stat_values($r) {
      if (is_null($this->statmac)) throw new HDA_ICode_Exception("State Function {$r} has not been defined");
	  if (is_null($r)) return array();
	  return array_merge($this->_do_stat_values($this->statmac->state_as($r)), $this->statmac->state_values($r));
	  }
   private function _do_stat_state($r) {
      if (is_null($this->statmac)) throw new HDA_ICode_Exception("State Function {$r} has not been defined");
	  if (is_null($r)) return null;
	//  return (($stat = $this->statmac->state_state($r)) || ($stat = $this->_do_stat_state($this->statmac->state_as($r))))?$stat:null;
	  $stat = $this->statmac->state_state($r);
      if (($stat===false) || is_null($stat)) $stat = $this->_do_stat_state($this->statmac->state_as($r));
	  return $stat;
	  }
   private function _do_stat_assign($r, $v, $prop) {
      if (is_null($this->statmac)) throw new HDA_ICode_Exception("State Function {$r} has not been defined");
	  return $this->statmac->state_assign($r, $v, $prop);
	  }
   private function _do_stat_extends($r, $v) {
      if (is_null($this->statmac)) throw new HDA_ICode_Exception("State Function {$r} has not been defined");
      return $this->statmac->state_extends($r, $v);
      }      
   

   private function _do_exit() {
      if ($this->_exit_code === true || ($this->_exit_code === 0 && $this->_exit_code !== false)) $this->debug_s .= "Exit with success\n";
      elseif ($this->_exit_code === false) $this->debug_s .= "Exit with false (failed)\n";
      else $this->debug_s .= "Exit with code {$this->_exit_code}\n";
      if (!is_null($this->lib)) $this->lib->_do_call_on_error_close(null, null);
      return true;
      }

   private function _do_exception($e, $v1) {
      switch ($e) {
        case 'ARGC': $msg_e = "Invalid parameter count for function {$v1} "; break;
        default: $msg_e = "Unknown exception code {$e}";
        }
      $msg_e = "Run time exception: {$msg_e}";
      $this->error[] = $msg_e;
      $this->debug_s .= $msg_e;
	  throw new HDA_ICode_Exception($msg_e);
      return false;
      }
	  
   public $on_debug_line = 0;
   private $break_table = null;

   public function dump_ns() {
      for ($i=0; $i<count($this->stns); $i++) {
         foreach ($this->stns[$i] as $k=>$p) {
            $this->debug_s .= "\n({$i}):{$k} =>[";
            $this->_dump_ns($p, 1);
            $this->debug_s .= "]";
            }
         }
      foreach($this->fns as $k=>$p) {
         $this->debug_s .= "\n{$k} [{$p}]";
         }
	  if (!is_null($this->statmac)) {
	     $this->debug_s .= "\nSTATE MC\n".$this->statmac->_get_mc()."\n";
	     }
      }
   private function _dump_ns($a, $level) {
	   $this->debug_s .= print_r($a, true);
	   return;
      if (is_array($a)) {
         foreach ($a as $k=>$p) {
            $this->debug_s .= "\n{$k} =>[";
            $this->_dump_ns($p, $level+1);
            $this->debug_s .= "]";
            }
         }
      elseif (is_object($a)) {
	     $this->debug_s .= print_r($a, true);
	     }
      else {
         $s = substr($a, 0, 20);
         $this->debug_s .= " ({$s}...) ";
         }
      }
   public function lookup($m) {
      if (array_key_exists('$$'.$m, $this->ns)) $m = '$$'.$m;
      elseif (array_key_exists('#'.$m, $this->ns)) $m = '##'.$m;
      elseif (array_key_exists('$'.$m, $this->ns)) $m = '$'.$m;
      elseif (!array_key_exists($m = '#'.$m, $this->ns)) return NULL;
      $p = $this->ns[$m];
      if (is_array($p)) return NULL;
      return $p;
      }
   public function _get_ns() {
      return $this->stns;
	  }
   public function _get_debug() {
      return $this->debug_s;
      }
   public function _debug($m) {
      $this->debug_s .= "{$m}\n";
      }
   public function _get_log() {
      $s = $this->trace_s."\n";
	  $s .= $this->lib->lastRunError;
      return $s;
      }
   public function _clear_logs() {
      $this->trace_s = "";
	  if (!is_null($this->lib)) return $this->lib->clear_console();
	  }
   public function _trace($m) {
      if ($this->trace_calls) $this->trace_s .= "{$m}\n";
      }
   public function _get_console() {
      if (!is_null($this->lib)) return $this->lib->get_console();
      return "";
      }
}

class _state_mc {

   private $_mc = null;
   public function __destruct() {
      unset($this->_mc);
	  $this->_mc = null;
      }
   public function __construct() {
      $this->_mc = array();
      }
	  
   public function state_exists($mc) {
      return array_key_exists($mc, $this->_mc);
	  }
	  
   public function state_as($mc) {
      if (!array_key_exists($mc, $this->_mc)) throw new HDA_ICode_Exception("State Function {$mc} has not been defined");
	  return $this->_mc[$mc]['extends'];
      }
   public function state_extends($mc, $as) {
      if (!array_key_exists($as, $this->_mc)) throw new HDA_ICode_Exception("State Function {$as} has not been defined");
      if (!array_key_exists($mc, $this->_mc)) {
	     $this->_new_mc($mc);
	     }
	  $this->_mc[$mc]['extends'] = $as;
	  return true;
	  }
   
	  
   public function state_change($mc, $v) {
      if (!array_key_exists($mc, $this->_mc)) throw new HDA_ICode_Exception("State Function {$mc} has not been defined");
	  $mce = $this->_mc[$mc]['mce'];
	  $stat = $this->_mc[$mc]['state'];
	  foreach ($mce as $a_mce) {
	     $matches=array();
	     if ($a_mce['stat_from']==$stat || $a_mce['stat_from']=='*') {
		    if ((strcasecmp($a_mce['pattern'],$v)==0)||
                (@preg_match("/{$a_mce['pattern']}/i",$v, $matches))) {
			   if ($a_mce['stat_to']!='*') $this->_mc[$mc]['state'] = $stat = $a_mce['stat_to'];
			   if (($a_mce['stat_do'] & 1)==0) {
			      $this->_mc[$mc]['tags'][$stat] = $v;
			      foreach($matches as $k=>$v) $this->_mc[$mc]['value'][$k] = trim($v);
			      return true;
				  }
			   return false;
			   }
			if (!is_null($a_mce['stat_back'])) {
			   $this->_mc[$mc]['state'] = $a_mce['stat_back'];
		       return false;
			   }
			}
	     }
	  return false;
      }
	  
   public function state_state($mc) {
      if (!array_key_exists($mc, $this->_mc)) throw new HDA_ICode_Exception("State Function {$mc} has not been defined");
	  $mce = $this->_mc[$mc]['mce'];
	  return $this->_mc[$mc]['state'];
      }
   public function state_property($mc, $prop) {
      if (!array_key_exists($mc, $this->_mc)) throw new HDA_ICode_Exception("State Function {$mc} has not been defined");
	  $mce = $this->_mc[$mc]['mce'];
	  switch ($prop) {
	     case '.':
		    $stat = $this->_mc[$mc]['state'];
			if (array_key_exists($stat, $this->_mc[$mc]['tags'])) return $this->_mc[$mc]['tags'][$stat];
		    break;
		 default:
	        if (array_key_exists($prop,$this->_mc[$mc]['value'])) return $this->_mc[$mc]['value'][$prop];
		    break;
		 }
	  return null;
      }
   public function state_values($mc) {
      if (!array_key_exists($mc, $this->_mc)) throw new HDA_ICode_Exception("State Function {$mc} has not been defined");
	  return $this->_mc[$mc]['value'];
      }
   public function state_assign($mc, $v, $prop) {
      if (!array_key_exists($mc, $this->_mc)) throw new HDA_ICode_Exception("State Function {$mc} has not been defined");
      switch ($prop) {
	     case '?':
		    foreach ($this->_mc[$mc]['mce'] as $def) {
			   if (strcasecmp($def['stat_to'], $v)==0) {
			      $this->_mc[$mc]['state'] = $def['stat_to'];
				  return true;
			      }
			   }
		    foreach ($this->_mc[$mc]['mce'] as $def) {
			   if (strcasecmp($def['stat_from'], $v)==0) {
			      $this->_mc[$mc]['state'] = $def['stat_from'];
				  return true;
			      }
			   }
			break;
		 case '.':
	        $stat = $this->_mc[$mc]['state'];
		    $this->_mc[$mc]['tags'][$stat] = $v;
		    return true;
		 default:
			$this->_mc[$mc]['value'][$prop] = $v;
		    return true;
         }    
      return false;		 
      }
   public function def_state($mc, $sfrom, $sto, $sback, $v, $skips) {
      if (!array_key_exists($mc, $this->_mc)) {
	     $this->_new_mc($mc);
	     }
	  $a['pattern'] = $v;
	  $a['stat_from'] = $sfrom;
	  $a['stat_to'] = $sto;
	  $a['stat_back'] = $sback;
	  $a['stat_do'] = ($skips)?1:0;
	  $this->_mc[$mc]['mce'][] = $a;
      }
	  
   private function _new_mc($mc) {
      $n = array('extends'=>null, 'state'=>null,'mce'=>array(),'value'=>array(),'tags'=>array());
	  $this->_mc[$mc] = $n;
	  }
	  
   public function _get_mc() {
      return print_r($this->_mc, true);
	  }

}

function HDA_CodeParser($path) {
   $the_log = "";
   if (!@file_exists($path)) $the_log .= "Fails to compile {$path}, file not found";
   else {
      $s = @file_get_contents($path);
      if ($s===false || strlen($s)==0) $the_log .= "Fails to read file {$path} or file empty";
	  else {
         $_compiler = new _parse_code($s);
         $execute = $_compiler->compile();
         if ($execute===false) {
            $the_log .= "Fails to compile\n";
            foreach ($_compiler->errors() as $error) $the_log .= "{$error}\n";
			}
		 else $the_log .= "Compiled ok";
		 }
	  }
   return $the_log;
   }
   

   
function HDA_CodeLayout($path, $filename) {
   if (strlen($path)>0) $path .= "/";
   $fpath = $path;
   $fpath .= $filename;
   $s = @file_get_contents($fpath);
   if ($s===false || strlen($s)==0) return "";
   $_layout = new _layout_code($s);
   return $_layout->layout();
   }
   
function HDA_CompilerExecute(&$process, $path, $filename, &$params_list, $and_run, &$the_log) {
   global $CONSOLE_log;
   global $glob_mem;
   $glob_mem = "";
   $running_debug = (($and_run&2)<>0);
   $running_monitor = (($and_run&4)<>0);
   $run_requested = (($and_run&1)<>0);
   if (strlen($path)>0) $path .= "/";
   $fpath = $path;
   $fpath .= $filename;
   $s = @file_get_contents($fpath);
   if ($s===false || strlen($s)==0) $the_log .= "Fails to read file or file empty";
   $CONSOLE_log = "Request to execute process ref {$process['Title']} at ".hda_db::hdadb()->PRO_DB_dateNow()."\n";
   $start_time = time();
   $_compiler = new _parse_code($s);
   $execute = $_compiler->compile();
   HDA_ProfileTags($process['ProfileItem'], $_compiler->metatags());
   $q = (array_key_exists('QueueLevel',$process))?$process['QueueLevel']:0;
   if (is_null($q)) $q = 0;
   $pendingq = (array_key_exists('ItemId',$process))?$process['ItemId']:null;
   $_vm = null;
   
   hda_db::hdadb()->HDA_DB_monitorRegister($process, session_id(), getmypid());
   $exit_code = true;
   if ($execute===false) {
      $exit_code = false;
      $the_log .= "Fails to compile in {$path}\n";
      foreach ($_compiler->errors() as $error) $the_log .= "{$error}\n";
      $CONSOLE_log .= $the_log;
      }
   else if ($run_requested) {
      $_vm = new _vm_code($execute);
     // @file_put_contents("{$path}debug.log", "{$the_log} {$CONSOLE_log}");
      @file_put_contents("{$path}console.log", $CONSOLE_log);
      $_vm->run($process, $params_list, $running_debug, $running_monitor);
      $the_log .= $_vm->_get_log()."\n";
      $the_log .= $_vm->_get_console()."\n";
      $exit_code = $_vm->_exit_code;
      $CONSOLE_log .= $_vm->_get_console();
      $exec_time = time() - $start_time;
      $end_time = "Ends execute process ref {$_vm->ref} at ".hda_db::hdadb()->PRO_DB_dateNow()." in {$exec_time}secs from Q {$_vm->q}\n";
      $CONSOLE_log .= $end_time;
      global $DEBUG_log;
      $_vm->dump_ns();
      $DEBUG_log = "{$the_log}\nDEBUG:\n".$_vm->_get_debug();
      @file_put_contents("{$path}debug.log", $DEBUG_log);
      }
   global $HDOCS;
   $HDOCS = $_compiler->hdocs();
   unset($_compiler);
   $_compiler = null;
   if (is_object($_vm)) {
      $_vm->_close_lib();
      unset($_vm);
      $_vm = null;
	  }
   unset($execute);
   $execute = null;
   
   if ($running_monitor) {
      hda_db::hdadb()->HDA_DB_monitor(0, 0, NULL, 'FINISHED');
      }
   else hda_db::hdadb()->HDA_DB_monitorClear(session_id());
   
   $exit_msg = "Ends with Exit Code ";
   if (($exit_code === 0 && $exit_code !== false) || ($exit_code===true)) $exit_msg .= "SUCCESS\n";
   elseif ($exit_code===false) $exit_msg .= "FALSE (Failed)\n";
   else $exit_msg .= "{$exit_code}\n";
   $the_log .= $exit_msg;
   $result = $exit_code;

   $CONSOLE_log .= "{$exit_msg}";
   $the_log = "\nConsole:\n{$CONSOLE_log}\nEnd Console Log\n";
   @file_put_contents("{$path}console.log", $CONSOLE_log);
   $dt = date('YmdGis');
   @file_put_contents("{$path}console_{$dt}.log", $CONSOLE_log);
   gc_collect_cycles();	  
   return $result;
   }
$DEBUG_log = "";
$CONSOLE_log = "";
$HDOCS = array();
?>