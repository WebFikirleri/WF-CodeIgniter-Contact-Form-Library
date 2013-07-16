<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
field_types null, text, password, dropdown, multiselect
 */

class Contact_form
{
  	protected $ci;
  	protected $fields = array();
  	protected $form_id = false;
  	public $errors = false;
  	public $messages = false;
  	public $submit_text = 'Mesajımı Gönder';
  	public $mail_title = '';
  	public $mail_subtitle = '';
  	public $mail_from_mail = '';
  	public $mail_from_name = '';
  	public $mail_to = '';
  	public $form_prepend = '';

	function __construct()
	{
        $this->ci =& get_instance();
        $this->ci->load->helper('string');
        $this->ci->load->library('form_validation');
        $this->ci->form_validation->set_error_delimiters('<span class="help-inline">', '</span>');
        $this->form_id = 'form-'.random_string('alnum', 8);
	}

	function render()
	{
		if ($this->ci->input->server('REQUEST_METHOD') == 'POST') {
			if ($this->_make_validation()) {
				return $this->_send();
			} else {
				$this->form_prepend = '<div class="alert alert-error">Formda hatalar oluştu! Lütfen düzeltip tekrar deneyiniz.</div>';
				return $this->_create_form();
			}
		} else {
			return $this->_create_form();
		}
	}

	function set_form_title($form_title)
	{
		$this->form_title = $form_title;
		return $this;
	}

	function set_mail_title($mail_title)
	{
		$this->mail_title = $mail_title;
		return $this;
	}

	function set_mail_subtitle($mail_subtitle)
	{
		$this->mail_subtitle = $mail_subtitle;
		return $this;
	}

	function set_mail_from_mail($mail_from_mail)
	{
		$this->mail_from_mail = $mail_from_mail;
		return $this;
	}

	function set_mail_from_name($mail_from_name)
	{
		$this->mail_from_name = $mail_from_name;
		return $this;
	}

	function set_mail_to($mail_to)
	{
		$this->mail_to = $mail_to;
		return $this;
	}

	function _send()
	{
		$ret = $this->_send_mail();
		if ($ret === true) {
			$out = '<div class="alert alert-success">Mesajınız başarı ile iletilmiştir!</div>';
		} else {
			$out = '<div class="alert alert-error">Mesajınız gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.'.$ret.'</div>';
			$out .= $this->_create_form();
		}
		return $out;
	}

	function add_field($field_name, $field_title, $field_vatermark = null, $validation = '')
	{
		$this->fields[$field_name] = array(
				'name' => $field_name,
				'title' => $field_title,
				'validation' => $validation,
				'field_type' => null,
				'field_watermark' => $field_vatermark,
				'field_values' => null
			);
		return $this;
	}

	function field_type($field_name, $field_type, $field_values = null)
	{
		$this->fields[$field_name]['field_type'] = $field_type;
		$this->fields[$field_name]['field_values'] = $field_values;

		return $this;
	}

	protected function _make_validation()
	{
		$this->ci->load->library('form_validation');

		foreach ($this->fields as $field) {
			if ($field['validation'] !== null)
				$this->ci->form_validation->set_rules($field['name'], $field['title'], $field['validation']);
		}

		$result = $this->ci->form_validation->run();

		return $result;
	}

	function _create_form($type = 'view')
	{
		$out = $this->form_prepend;
		$out .= form_open(current_url(), array('class'=>'form-horizontal','id'=>$this->form_id));

		foreach ($this->fields as $key => $field) {
			$err_class = (form_error($field['name'])) ? ' error' : '';
			$err_text = (form_error($field['name'])) ? form_error($field['name']) : '';

			if ($field['field_type'] == null OR $field['field_type'] == 'text' OR $field['field_type'] == 'password') {
				$value = (set_value($field['name'])) ? set_value($field['name']) : $field['field_values'];
				$type = ($field['field_type'] == null OR $field['field_type'] == 'text') ? 'text' : 'password';
				$out .= '<div class="control-group'.$err_class.'">
				    <label class="control-label" for="'.$field['name'].'">'.$field['title'].'</label>
				    <div class="controls">
				      <input type="'.$type.'" name="'.$field['name'].'" id="'.$field['name'].'" placeholder="'.$field['field_watermark'].'" value="'.$value.'" class="input-block-level">'.$err_text.'
				    </div>
				  </div>';
			} elseif ($field['field_type'] == 'textarea') {
				$value = (set_value($field['name'])) ? set_value($field['name']) : $field['field_values'];
				$out .= '<div class="control-group'.$err_class.'">
				    <label class="control-label" for="'.$field['name'].'">'.$field['title'].'</label>
				    <div class="controls">
				    	<textarea rows="3" name="'.$field['name'].'" id="'.$field['name'].'" placeholder="'.$field['field_watermark'].'" class="input-block-level">'.$value.'</textarea>'.$err_text.'
				    </div>
				  </div>';
			} elseif ($field['field_type'] == 'dropdown') {
				$value = (set_value($field['name'])) ? set_value($field['name']) : '';
				$out .= '<div class="control-group">
				    <label class="control-label" for="'.$field['name'].'">'.$field['title'].'</label>
				    <div class="controls">
				    	<select name="'.$field['name'].'" id="'.$field['name'].'" placeholder="'.$field['field_watermark'].'" class="input-block-level">';
				foreach ($field['field_values'] as $key => $val) {
					if (is_numeric($key)) {
						if ($value === $val) {
							$out .= '<option value="'.$val.'" selected="selected">'.$val.'</option>';
						} else {
							$out .= '<option value="'.$val.'">'.$val.'</option>';
						}
					} else {
						if ($value === $key) {
							$out .= '<option value="'.$key.'" selected="selected">'.$val.'</option>';
						} else {
							$out .= '<option value="'.$key.'">'.$val.'</option>';
						}
					}
				}
				$out .= '</select>
				    </div>
				  </div>';
			} elseif ($field['field_type'] == 'multiselect') {
				$value = (set_value($field['name'])) ? set_value($field['name']) : '';
				$out .= '<div class="control-group">
				    <label class="control-label" for="'.$field['name'].'">'.$field['title'].'</label>
				    <div class="controls">
				    	<select name="'.$field['name'].'" id="'.$field['name'].'" placeholder="'.$field['field_watermark'].'" class="input-block-level"  multiple="multiple">';
				foreach ($field['field_values'] as $key => $val) {
					if (is_numeric($key)) {
						if ($value === $val) {
							$out .= '<option value="'.$val.'" selected="selected">'.$val.'</option>';
						} else {
							$out .= '<option value="'.$val.'">'.$val.'</option>';
						}
					} else {
						if ($value === $key) {
							$out .= '<option value="'.$key.'" selected="selected">'.$val.'</option>';
						} else {
							$out .= '<option value="'.$key.'">'.$val.'</option>';
						}
					}
				}
				$out .= '</select>
				    </div>
				  </div>';
			} elseif ($field['field_type'] == 'checkboxes') {
				$value = (set_value($field['name'])) ? set_value($field['name']) : $field['field_values'];
				$type = 'text';
				$out .= '<div class="control-group">
				  <label class="control-label">'.$field['title'].'</label>
				  <div class="controls">';
				  foreach ($field['field_values'] as $key => $val) {
				  	$out .='<label class="checkbox">
				      <input type="checkbox" name="'.$field['name'].'[]" id="'.$field['name'].'" value="'.$val.'">
				      '.$val.'
				    </label>';
				  }
				  $out .= '</div>
				</div>';
			} elseif ($field['field_type'] == 'file') {
				$value = (set_value($field['name'])) ? set_value($field['name']) : $field['field_values'];
				$type = 'file';
				$out .= '<div class="control-group'.$err_class.'">
				    <label class="control-label" for="'.$field['name'].'">'.$field['title'].'</label>
				    <div class="controls">
				      <input type="'.$type.'" name="'.$field['name'].'" id="'.$field['name'].'" placeholder="'.$field['field_watermark'].'" value="'.$value.'" class="input-block-level">'.$err_text.'
				    </div>
				  </div>';
			} else {
				$value = (set_value($field['name'])) ? set_value($field['name']) : $field['field_values'];
				$type = 'text';
				$out .= '<div class="control-group'.$err_class.'">
				    <label class="control-label" for="'.$field['name'].'">'.$field['title'].'</label>
				    <div class="controls">
				      <input type="'.$type.'" name="'.$field['name'].'" id="'.$field['name'].'" placeholder="'.$field['field_watermark'].'" value="'.$value.'" class="input-block-level">'.$err_text.'
				    </div>
				  </div>';
			}
		}
		$out .= '<div class="control-group">
		    <div class="controls">
		      <button type="submit" class="btn">'.$this->submit_text.'</button>
		    </div>
		  </div>';
		$out .= form_close();
		return $out;
	}

	function _create_mail()
	{
		$inmail = $this->_create_message_mail();
		return $this->_mail_bootstrap($inmail);
	}

	function _create_message_mail()
	{
		$out = '<h3>'.$this->mail_title.'&nbsp;</h3><hr style="width:100%;height:1px;border-bottom:1px solid #FDFDFD;margin-bottom:16px;background:#F6F6F6;color:#F6F6F6;" />';
		$out .= '<table width="100%" border="0" cellspacing="0" cellpadding="8"><tr> <th width="200" align="left" bgcolor="#F9F9F9">Form Alanı</th> <th width="8" align="center" bgcolor="#F9F9F9" class="nl">:</th> <th align="left" bgcolor="#F9F9F9">Alana Ait Değer</th> </tr>';
		foreach ($this->fields as $key => $field) {
			$vals = (is_array($this->ci->input->post($field['name'],true))) ? implode(', ', $this->ci->input->post($field['name'],true)) : set_value($field['name']);
			$out .= '<tr><td align="left" valign="top" bgcolor="#FDFDFD">'.$field['title'].'&nbsp;</td><td align="center" valign="top" bgcolor="#FDFDFD" class="nl">:</td><td align="left" valign="top">'.$vals.'&nbsp;</td></tr>';
		}
		$out .= '</table>';
		$out .= '<p style="color:#ccc"><small>'.$this->mail_subtitle.'&nbsp;</small></p>';
		return $this->_mail_bootstrap($out);
	}

	function _mail_bootstrap($value='')
	{
		$out = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Your Message Subject or Title</title>
<style type="text/css">#outlook a{padding:0}body{width:100%!important;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;margin:0;padding:0}.ExternalClass{width:100%}.ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div{line-height:100%}#backgroundTable{margin:0;padding:0;width:100%!important;line-height:100%!important}img{outline:0;text-decoration:none;-ms-interpolation-mode:bicubic}a img{border:0}.image_fix{display:block}p{margin:1em 0}h1,h2,h3,h4,h5,h6{color:black!important}h1 a,h2 a,h3 a,h4 a,h5 a,h6 a{color:blue!important}h1 a:active,h2 a:active,h3 a:active,h4 a:active,h5 a:active,h6 a:active{color:red!important}h1 a:visited,h2 a:visited,h3 a:visited,h4 a:visited,h5 a:visited,h6 a:visited{color:purple!important}table td{border-collapse:collapse}table{border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0}a{color:orange}@media only screen and (max-device-width:480px){a[href^="tel"],a[href^="sms"]{text-decoration:none;color:black;pointer-events:none;cursor:default}.mobile_link a[href^="tel"],.mobile_link a[href^="sms"]{text-decoration:default;color:orange!important;pointer-events:auto;cursor:default}}@media only screen and (min-device-width:768px) and (max-device-width:1024px){a[href^="tel"],a[href^="sms"]{text-decoration:none;color:blue;pointer-events:none;cursor:default}.mobile_link a[href^="tel"],.mobile_link a[href^="sms"]{text-decoration:default;color:orange!important;pointer-events:auto;cursor:default}}
table{border-top:1px solid #dfdfdf;border-right:1px solid #dfdfdf}table td,table th{border-bottom:1px solid #dfdfdf;border-left:1px solid #dfdfdf}table td.nl,table th.nl{border-left:none}
body{font-family:Arial, Helvetica, sans-serif;font-size:14px;color:#666;}</style>
<!--[if IEMobile 7]>
<style type="text/css"></style>
<![endif]-->
<!--[if gte mso 9]>
<style></style>
<![endif]-->
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="32">
  <tr>
    <td>
EOF;
	$out .= $value;
	$out .= <<<EOF
    </td>
  </tr>
</table>
</body>
</html>
EOF;
		return $out;
	}

	function _send_mail()
	{
		$this->ci->load->library('email');

		$this->ci->email->from($this->mail_from_mail, $this->mail_from_name);
		$this->ci->email->to($this->mail_to);

		$this->ci->email->subject($this->mail_title);
		$this->ci->email->message($this->_create_mail());	

		if ($this->ci->email->send())
			return true;
		else
			return $this->ci->email->print_debugger();
	}

}

/* End of file contact_form.php */
/* Location: modules/site/libraries/contact_form.php */
