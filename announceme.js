function setCookie(c_name, value, exdays) {
 var exdate = new Date();
 exdate.setDate(exdate.getDate()+exdays);
 var c_value=escape(value) + ((exdays==null) ? "" : ";expires="+exdate.toUTCString());
 document.cookie=c_name+"="+c_value;
}
$(document).ready(function() {
 om = 0;
 if($("html").css('margin-top')!="auto") {
  om = parseInt($("body").css('margin-top'));
 }
 aa = 0;
 $(".announceme").each(function() {
  aa += 41;
 });
 $("html").css({'margin-top':(om+aa)+"px !important"});
 mt = parseInt($("html").css('margin-top'));
 $(".announceme-close").click(function() {
  $(this).parent().slideUp(300);
  mt = mt-41;
  $("html").animate({'margin-top':mt+"px !important"},300);
 });
 $(".announceme-del").click(function() {
  id = $(this).parent().attr('id');
  $(this).parent().slideUp(300);
  mt = mt-41;
  $("html").animate({'margin-top':mt+"px !important"},300);
  setCookie(id,'deleted',1000000);
 });
});
