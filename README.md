WebFikirleri CodeIgniter Contact Form Library
===================================

WebFikirleri CodeIgniter Contact Form Library - Easy build Contact Forms

##Example Usage:

    $this->load->library('contact_form');

    $this->contact_form->set_mail_title('Contact Form')
            ->set_form_title('Contact Form')
            ->set_mail_title('New Message')
            ->set_mail_to('info@yoursite.com')
            ->set_mail_from_name('WebFikirleri')
            ->set_mail_from_mail('no-reply@yoursite.com');

    $this->contact_form->add_field('name','Name',null,'required')
            ->add_field('phone','Phone',null,'required')
            ->add_field('email','Mail',null,'required|valid_email')
            ->add_field('subject','Subject',null,'required')
            ->add_field('message','Message',null,'required');

    $this->contact_form->field_type('message','textarea');

    $form = $this->contact_form->render();

## Example File Upload

    $this->contact_form->add_field('image_field','Image File','required');
    $this->contact_form->field_type('image_field','file');

[![Hits](https://hits.seeyoufarm.com/api/count/incr/badge.svg?url=https%3A%2F%2Fgithub.com%2FWebFikirleri%2FWF-CodeIgniter-Contact-Form-Library&count_bg=%233D8FC8&title_bg=%23555555&icon=microsoftacademic.svg&icon_color=%23E7E7E7&title=VISITS&edge_flat=true)](https://hits.seeyoufarm.com)
