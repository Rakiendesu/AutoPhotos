<?php
/**
 * 插件原理来自https://blog.imalan.cn/archives/282/<br>
 * 基于<a href="https://github.com/nk-o/flickr-justified-gallery#wordpress-plugin">Flickr's Justified Gallery</a>的瀑布流图片效果
 *   
 * @package AutoPhotos
 * @author <a href="https://qqdie.com">泽泽社长</a>/<a href="https://rakien.top">Rakien</a>
 * @version 2.0.1
 * @link 
 */
class AutoPhotos_Plugin implements Typecho_Plugin_Interface
{
	/**
	 * 激活插件方法,如果激活失败,直接抛出异常
	 * 
	 * @access public
	 * @return void
	 * @throws Typecho_Plugin_Exception
	 */
	public static function activate()
	{
        Typecho_Plugin::factory('Widget_Archive')->header = array('AutoPhotos_Plugin', 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('AutoPhotos_Plugin', 'footer');
		Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('AutoPhotos_Plugin','tutu');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('AutoPhotos_Plugin','tus');
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array('AutoPhotos_Plugin', 'button');
        Typecho_Plugin::factory('admin/write-page.php')->bottom = array('AutoPhotos_Plugin', 'button');
	}
	/* 禁用插件方法 */
	public static function deactivate(){}
	public static function config(Typecho_Widget_Helper_Form $form){
      
     $hz = new Typecho_Widget_Helper_Form_Element_Text('hz', NULL,NULL,'图片后缀', _t('为图片添加后缀，一般用于cdn图片裁剪，不填则显示原图'));
$form->addInput($hz); 
     $fhz = new Typecho_Widget_Helper_Form_Element_Text('fhz', NULL,NULL,'灯箱图片后缀', _t('为灯箱图片添加后缀，一般用于cdn图片裁剪，不填则显示原图'));
$form->addInput($fhz); 
      
     $tuozhan = new Typecho_Widget_Helper_Form_Element_Checkbox('tuozhan', 
    array('jq' => _t('加载jquery，当你启动插件功能不生效时请勾选此项'),
          'xbt' => _t('勾选此项隐藏图片下方小标题'),
          'fancybox' => _t('使用fancybox图片灯箱插件，如果您的模板已经使用了fancybox，这项就无需勾选，勾选了反而可能会出问题'),
),
    array(), _t('拓展设置'), _t('<h4>插件使用方法：</h4><p style="background: #fff;padding: 10px;border-radius: 5px;">
使用<code>[photos][/photos]</code>包裹需要显示在一行的单个或者多个图片，并且<code>[photos][/photos]</code>前后要多加一个换行，如：<br>
<br>
[myPhotos]<br>
![图1.jpg][1]<br>
![图2.jpg][2]<br>
![图3.jpg][2]<br>
[/myPhotos]<br><br>
[myPhotos]<br>
![图4.jpg][4]<br>
![图5.jpg][5]<br>
[/myPhotos]<br><br>
2，使用<code>[PhotoList][/PhotoList]</code>瀑布流相册
</p>

'));
    $form->addInput($tuozhan->multiMode());

    $jianju = new Typecho_Widget_Helper_Form_Element_Text('jianju', NULL,NULL,'图片间距', _t('图片之间的间距，请填写数字'));
$form->addInput($jianju);
      
    $yuanjiao = new Typecho_Widget_Helper_Form_Element_Text('yuanjiao', NULL,NULL,'图片圆角', _t('图片圆角幅度，请填写数字'));
$form->addInput($yuanjiao); 
      
      
    }
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

  
  public static function header()
  {
	  //插入图片瀑布流js和css
	  echo '<script src="'.Helper::options()->pluginUrl . '/AutoPhotos/dist/fjGallery.js"></script><link href="'.Helper::options()->pluginUrl . '/AutoPhotos/dist/fjGallery.css" rel="stylesheet">';
	   $m=Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->jianju;
	   $r=Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->yuanjiao;
		echo '<link rel="stylesheet" href="'.Helper::options()->pluginUrl . '/AutoPhotos/style.css?20200804">';
	if (!empty(Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan) && in_array('fancybox',  Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan)){
	echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css">';
	}
   if($r||$m){
   ?>
<style>
<?php if($m){
?>
div.photos figure {
    position: relative;
    margin: auto <?php echo $m; ?>px;
}
div.photos,.ze-row {
    margin: auto -<?php echo $m; ?>px;
}
.zemedia{
margin: <?php echo $m; ?>px;
}
<?php } if($r){ ?>
div.photos figure div img,.zemedia {
    border-radius: <?php echo $r; ?>px !important;
}
<?php } ?>
</style>
<?php 
   }
  }
  public static function footer()
  {
	//插入瀑布流图片初始化代码：
	echo "<script>fjGallery(document.querySelectorAll('.my-fj-gallery'), {
	 rowHeight: 250,
     itemSelector: '.fj-gallery-item'
    });</script>";
	
	
	if (!empty(Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan) && in_array('jq',  Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan)){
	echo '<script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>';}
		
	if (!empty(Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan) && in_array('fancybox',  Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan)){
	echo '<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>';}     
  ?>
<script>
(function(){
  var base = 50;
  $.each($('.photos'), function(i, photoSet){
    $.each($(photoSet).children(), function(j, item){
      var img = new Image();
      img.src = $(item).find('img').attr('src');
      img.onload = function(){
        var w = parseFloat(img.width);
        var h = parseFloat(img.height);
        $(item).css('width', w*base/h +'px');
        $(item).css('flex-grow', w*base/h);
        $(item).find('div').css('padding-top', h/w*100+'%');
      };
    });
  }); 
})();
</script>
<?php
  }
	public static function tutu($text, $ojbk, $last)
    {
$text = empty($last) ? $text : $last;
$text = self::parsePhotoSet($text);
$text = self::parsePhotolistSet($text);
return $text; 
    }
  
static public function parsePhotoSet($content)
    {
        $reg = '/\[myPhotos(.*?)\/myPhotos\]/s';
        $new = preg_replace_callback($reg, array('AutoPhotos_Plugin', 'parsePhotoSetCallBack'), $content);
        $reg='/<p>\[myPhotos.*?\](.*?)\[\/myPhotos\]<\/p>/s';
        $rp='';
        $rp = '<div class="photos">${1}</div>';
        $new=preg_replace($reg, $rp, $new);
        return $new;
    }
 
static public function parsePhotolistSet($content)
    {
        $regx = '/\[PhotoList(.*?)\/PhotoList\]/s';
        $new = preg_replace_callback($regx, array('AutoPhotos_Plugin', 'parsePhotolistSetCallBack'), $content);
        $reg='/<p>\[PhotoList.*?\](.*?)\[\/PhotoList\]<\/p>/s';
        $rp='';
        $rp = '<div class="my-fj-gallery">${1}</div>';
        $new=preg_replace($reg, $rp, $new);
        return $new;
    }
  
  
private static function parsePhotoSetCallBack($match)
    {$hz=Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->hz;
     $fhz=Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->fhz;
$new='[myPhotos'. str_replace(['<br>', '<p>', '</p>'], '', $match[1]) .'/myPhotos]';
$regx = '/<img.*?src="(.*?)".*?alt="(.*?)".*?>/s';
if (!empty(Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan) && in_array('xbt',  Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan)){
if (!empty(Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan) && in_array('fancybox',  Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan)){
$new = preg_replace($regx, '<figure><div><a data-fancybox="gallery" href="$1" data-caption="$2"><img alt="$2" src="$1'.$hz.'" class="fig-image"></a></div></figure>', $new);
}else{  
  
$new = preg_replace($regx, '<figure><div><img alt="$2" src="$1'.$hz.'"></div></figure>', $new);
}}else{
if (!empty(Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan) && in_array('fancybox',  Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan)){
$new = preg_replace($regx, '<figure><div><a data-fancybox="gallery" href="$1'.$fhz.'" data-caption="$2"><img alt="$2" src="$1'.$hz.'" class="fig-image"></a></div><figcaption>$2</figcaption></figure>', $new);
}else{ 
$new = preg_replace($regx, '<figure><div><img alt="$2" src="$1'.$hz.'"></div><figcaption>$2</figcaption></figure>', $new);
}}
  return $new;
    }
private static function parsePhotolistSetCallBack($match)
    {
		$hz=Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->hz;
		$fhz=Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->fhz;
		$new='[PhotoList'. str_replace(['<br>', '<p>', '</p>'], '', $match[1]) .'/PhotoList]';
		$regx = '/<img.*?src="(.*?)".*?alt="(.*?)".*?>/s';
		  
		if (!empty(Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan) && in_array('fancybox',  Typecho_Widget::widget('Widget_Options')->plugin('AutoPhotos')->tuozhan)){
			$new = preg_replace($regx, '<div class="zecol-6">
			<div class="zemedia"><a data-fancybox="gallery" href="$1'.$fz.'" class="zemedia-content" title="$2" style="background-image: url(&quot;$1'.$hz.'&quot;);"></a></div></a></div>', $new);
		}else{
			$new = preg_replace($regx, '<div class="fj-gallery-item"><img src=$1'.$hz.' alt=""></div>', $new);
		}  
	
		  return $new;
    }  
  
  
public static function button(){
  ?>
<style>.Posthelper a{cursor: pointer; padding: 0px 6px; margin: 2px 0;display: inline-block;border-radius: 2px;text-decoration: none;}
.Posthelper a:hover{background: #ccc;color: #fff;}
</style>
<script>
function zeze(tag) {
					var myField;
					if (document.getElementById('text') && document.getElementById('text').type == 'textarea') {
						myField = document.getElementById('text');
					} else {
						return false;
					}
					if (document.selection) {
						myField.focus();
						sel = document.selection.createRange();
						sel.text = tag;
						myField.focus();
					}
					else if (myField.selectionStart || myField.selectionStart == '0') {
						var startPos = myField.selectionStart;
						var endPos = myField.selectionEnd;
						var cursorPos = startPos;
						myField.value = myField.value.substring(0, startPos)
						+ tag
						+ myField.value.substring(endPos, myField.value.length);
						cursorPos += tag.length;
						myField.focus();
						myField.selectionStart = cursorPos;
						myField.selectionEnd = cursorPos;
					} else {
						myField.value += tag;
						myField.focus();
					}
				}
  function Photos () {
var rs = "\n[myPhotos]\n请在这里插入图片，这里的图片将显示在同一行\n[/myPhotos]\n";
    zeze(rs);
    }
  function PhotoList () {
var rs = "\n[PhotoList]\n请在这里插入所有图片，这里的图片将展示出照片列表效果\n[/PhotoList]\n";
    zeze(rs);
    }
  
  $(document).ready(function(){
    $('#file-list').after('<div class="Posthelper"><a class="w-100" onclick=\"Photos()\" style="background: #E9E9E6;text-align: center;padding: 5px 0;color: #1344ff;">插入图集</a><a class="w-100" onclick=\"PhotoList()\" style="background: #E9E9E6;text-align: center;padding: 5px 0;color: #1344ff;">插入相册</a></div>');
  });
  </script>
<?php
}  
public static function tus($text, $ojbk, $last)
{
$text = empty($last) ? $text : $last;
$text = str_replace('[myPhotos]', '', $text);
$text = str_replace('[/myPhotos]', '', $text);
return $text; 
}  
}