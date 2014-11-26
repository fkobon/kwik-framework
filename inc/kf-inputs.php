<?php

  Class KwikInputs{

    public function positions() {
      $t = 'Top';$r = 'Right';$b = 'Bottom';$l = 'Left';$m = 'Middle';
      $c = 'Center';$z = '0';$s = ' ';$f = '50%';$o = '100%';
      $positions = array(
        $z.$s.$z => $t.$s.$l,$z.$s.$f => $t.$s.$c,$z.$s.$o => $t.$s.$r,$f.$s.$z => $m.$s.$l,
        $f.$s.$f => $m.$s.$c,$f.$s.$o => $m.$s.$r,$o.$s.$z => $b.$s.$l,$o.$s.$f => $b.$s.$c,
        $o.$s.$o => $b.$s.$r
      );
      return $positions;
    }

    public function repeat() {
      $R = 'Repeat'; $r = strtolower($R);
      return array(
        'no-'.$r => 'No '.$R,
        $r => $R,
        $r.'-x' => $R.'-X',
        $r.'-y' => $R.'-Y',
      );
    }

    public function target() {
      $target = array(
        '_blank' => 'New Window/Tab',
        '_self' => 'Same Window'
      );
      return $target;
    }

    public function bgSize() {
      $bgSize = array(
        'auto' => 'Default',
        '100% 100%' => 'Stretch',
        'cover' => 'Cover',
      );
      return $bgSize;
    }

    public function bgAttachment() {
      $bgAttachment = array(
        'scroll' => 'Scroll',
        'fixed' => 'Fixed',
      );
      return $bgAttachment;
    }

    public function fontWeights() {
      $fontWeights = array(
        'normal' => 'Normal',
        'bold' => 'Bold',
        'bolder' => 'Bolder',
        'lighter' => 'Lighter',
      );
      return $fontWeights;
    }


    public function multi($name, $value, $args) {
      $output = '';
      $fields = $args['fields'];
      foreach ($fields as $k => $v) {
        $val = $value[$k] ? $value[$k] : $args['fields'][$k]['value'];
        if($v['type'] === 'select'){
          $output .= $this->$v['type'](
            $name.'['.$k.']', // name
            $value[$k], // value
            $v['title'], // label
            $v['options'],
            $v['attrs']
          );
        } else {
          $output .= $this->$v['type'](
            $name.'['.$k.']', // name
            $val, // value
            $v['title'], // label
            $v['attrs']
          );
        }
      }

      return self::markup('div', $output, array('class' => 'kf_field kf_multi_field'));
    }


    /**
     * Generate markup for input field
     * @param  [Object] $attrs Object with properties for field attributes
     * @return [String]        markup for desired input field
     */
    public function input($attrs) {
      $output = '';
      if($attrs['label'] && !is_null($attrs['label'])) {
        $output = $this->markup('label', $attrs['label'], array( 'for' => $attrs['id']));
      }
      unset($attrs['label']);
      $output .= '<input ' . $this->attrs($attrs) . ' />';

      if ($attrs['value'] && $attrs['class'] === 'cpicker') {
        $output .= $this->markup('span', NULL, array('class'=>'clear_color', 'title'=>__('Remove Color', 'kwik')));
      }

      if($attrs['type'] !== 'hidden' && !is_null($attrs['type'])){
        $output = $this->markup('div', $output, array('class'=>KF_PREFIX.'field kf_'.$attrs['type'].'_wrap'));
      }
      return $output;
    }


    /**
     * Custom image input that uses the wordpress media library for uploading and storage
     * @param  [string] $name  name of input
     * @param  [string] $val   id of stored image
     * @param  [string] $label
     * @param  [array]  $attrs additional attributes. Can customize size of image.
     * @return [string] returns markup for image input field
     */
    public function img($name, $val, $label, $attrs) {
      if(!$attrs){
        $attrs = array();
      }
      wp_enqueue_media();
      $output = '';
      if($val && !empty($val)){
        if($attrs['img_size']){
          $img_size = $attrs['img_size'];
          unset($attrs['img_size']);
        } else {
          $img_size = 'thumbnail';
        }
        $thumb = wp_get_attachment_image_src($val, $img_size);
        $thumb = $thumb['0'];
        $img_title = get_the_title($val);
        $remove_img = $this->markup('span', NULL, array('title'=>__('Remove Image', 'kwik'), 'class' => 'clear_img tooltip') );
      }
      $defaultAttrs = array(
        'type' => 'hidden',
        'name' => $name,
        'class' => 'img_id',
        'value' => $val,
        'id' => $this->makeID($name)
      );
      $attrs = array_merge($defaultAttrs, $attrs);

      $img_attrs = array("src"=> $thumb, "class"=>"img_prev", "width"=>"23", "height"=>"23", "title"=>$img_title);

      $output .= $this->input($attrs);
      if($label) {
        $output .= $this->markup('label', esc_attr($label));
      }
      $output .= $this->markup('img', NULL, $img_attrs);
      if($thumb){
        $img_ttl = get_the_title($val);
        $img_ttl = $img_ttl.$this->markup('span', NULL, array( "class" => "clear_img", "tooltip" => __('Remove Image', 'kwik')));
      } else {
        $img_ttl = NULL;
      }
      $output .= $this->markup('span', $img_ttl, array('class'=>"img_title"));
      $output .= $this->markup('button', '+ '.__('IMG', 'kwik'), array('class'=>"upload_img", "type"=>"button"));
      $output = $this->markup('div', $output, array('class'=>KF_PREFIX.'field kf_img_wrap'));
      return $output;
    }

    public function text($name, $val, $label = NULL, $attrs = NULL) {
      $output = '';
      $defaultAttrs =   array(
        'type' => 'text',
        'name' => $name,
        'class' => KF_PREFIX.'text '. $this->makeID($name),
        'value' => $val,
        // 'id' => $this->makeID($name),
        'label' => esc_attr($label)
      );

      $attrs = !is_null($attrs) ? array_merge($defaultAttrs, $attrs) : $defaultAttrs;

      $output .= $this->input($attrs);

      return $output;
    }

    public function link($name, $val, $label = NULL, $attrs = NULL) {
      $output = '';

      $defaultAttrs =   array(
        'type' => 'text',
        'name' => $name."[url]",
        'class' => KF_PREFIX.'link '.$this->makeID($name),
        'value' => $val['url'],
        // 'id' => $this->makeID($name)
      );

      if(!is_null($attrs)){
        $attrs = array_merge($defaultAttrs, $attrs);
      }

      if($label) {
        $attrs['label'] = esc_attr($label);
      }

      $output .= $this->input($attrs);
      $output .= $this->select($name."[target]", $val['target'], NULL, NULL, $this->target());
      $output = $this->markup('div', $output, array('class'=>KF_PREFIX.'link_wrap'));

      return $output;
    }

    public function nonce($name, $val) {
      $attrs = array(
        'type' => 'hidden',
        'name' => $name,
        'value' => $val,
      );
      return $this->input($attrs);
    }

    public function spinner($name, $val, $label = NULL, $attrs = NULL) {
      $output = '';
      $defaultAttrs = array(
        'type' => 'number',
        'name' => $name,
        'class' => KF_PREFIX.'spinner',
        'max' => '50',
        'min' => '1',
        'value' => $val,
        'label'=> esc_attr($label)
      );

      $attrs = !is_null($attrs) ? array_merge($defaultAttrs, $attrs) : $defaultAttrs;

      $output .= $this->input($attrs);

      return $output;
    }

    public function color($name, $val, $label = NULL) {
      $output = '';
      wp_enqueue_script('cpicker', KF_URL . '/js/cpicker.js');

      $attrs = array(
        'type' => 'text',
        'name' => $name,
        'class' => 'cpicker',
        'value' => $val,
        'id' => $this->makeID($name),
        'label' => esc_attr($label)
      );
      $output .= $this->input($attrs);

      $output = $this->markup('div', $output, array('class'=> array(KF_PREFIX.'field_color', KF_PREFIX.'field')));

      return $output;
    }

    public function toggle($name, $val, $label = NULL, $attrs = NULL) {
      $output = '';

      wp_enqueue_script('kcToggle-js', 'http://kevinchappell.github.io/kcToggle/kcToggle.js', array('jquery'));
      wp_enqueue_style('kcToggle-css', 'http://kevinchappell.github.io/kcToggle/kcToggle.css', false);

      $defaultAttrs = array(
        'type' => 'checkbox',
        'name' => $name,
        'class' => 'kcToggle',
        'value' => $val || true,
        'id' => $this->makeID($name),
        'label' => esc_attr($label),
        'kcToggle' => NULL
      );
      if(!is_null($val) && $val !== ""){
        $defaultAttrs["checked"] = "checked";
      }

      $attrs = !is_null($attrs) ? array_merge($defaultAttrs, $attrs) : $defaultAttrs;

      $output .= $this->input($attrs);
      $output = $this->markup('div', $output, array('class'=>'kf_field_toggle'));

      return $output;
    }

    public function select($name, $val, $label = NULL, $attrs = NULL, $optionsArray) {
      $defaultAttrs = array(
        'name' => $name,
        'class' => KF_PREFIX.'select '.$this->makeID($name),
        'id' => $this->makeID($name)
      );

      $attrs = !is_null($attrs) ? array_merge($defaultAttrs, $attrs) : $defaultAttrs;

      $output = '';

      if($label) {
        $output .= $this->markup('label', esc_attr($label), array( 'for' => $attrs['id']));
      }
        $options = '';

        foreach ($optionsArray as $k => $v) {
          $oAttrs = array('value' => $k);
          if ($val === $k) {
            $oAttrs['selected'] = 'selected';
          }
          $options .= $this->markup('option', $v, $oAttrs);
        }

      $output .= $this->markup('select', $options, $attrs);
      $output = $this->markup('div', $output, array('class'=>KF_PREFIX.'field '.KF_PREFIX.'select_wrap'));

      return $output;
    }

    public function fontFamily($name, $val) {
      $utils = new KwikUtils();
      $fonts = $utils->get_google_fonts($api_key);  // TODO: Api key from settings
      $options = array();
      foreach ($fonts as $font) {
        $options[str_replace(' ', '+', $font->family)] = $font->family;
      }
      return $this->select($name, $val, $options);
    }


    /**
     * Takes an array of attributes and expands and returns them formatted for markup
     * @param  [Array] $attrs Array of attributes
     * @return [String]       attributes as strings ie. `name="the_name" class="the_class"`
     */
    private function attrs($attrs) {
      $output = '';
      if (is_array($attrs)) {
        if($attrs['label']) {
          unset($attrs['label']);
        }
        foreach ($attrs as $key => $val) {
          if (is_array($val)) {
            $val = implode(" ", $val);
          } elseif(!$val) {
            $val = ' ';
          }
          if($val !== ' ') $val = '="'.esc_attr($val).'"';
          $output .= $key . $val;
        }
      }
      return $output;
    }

    private function makeID($string){
      $string = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
      return trim(preg_replace('/-+/', '-', $string), '-');
    }

    public function markup($tag, $content = NULL, $attrs = NULL){
      $no_close_tags = array('img', 'hr', 'br'); $no_close = in_array($tag, $no_close_tags);

      $markup = '<'.$tag.' '.self::attrs($attrs).' '.($no_close ? '/' : '').'>';
      if($content){
        $c = '';
        if(is_array($content)){
          foreach ($content as $key => $value) {
            if(is_array($value)){
              $c .= implode($value);
            } elseif (is_string($value)) {
              $c .= $value;
            }
          }
        } else{
          $c = $content;
        }
        $markup .= $c;
      }
      if(!$no_close) $markup .= '</'.$tag.'>';

      return $markup;
    }


  }//---------/ Class KwikInputs
