!function(t){function e(t,e){return"function"==typeof t?t.call(e):t}function i(t){for(;t=t.parentNode;)if(t==document)return!0;return!1}function s(e,i){this.$element=t(e),this.options=i,this.enabled=!0,this.fixTitle()}s.prototype={show:function(){var i=this.getTitle();if(i&&this.enabled){var s=this.tip();s.find(".tipsy-inner")[this.options.html?"html":"text"](i),s[0].className="tipsy",s.remove().css({top:0,left:0,visibility:"hidden",display:"block"}).prependTo(document.body);var n=t.extend({},this.$element.offset(),{width:this.$element[0].offsetWidth,height:this.$element[0].offsetHeight}),o=s[0].offsetWidth,l=s[0].offsetHeight,a=e(this.options.gravity,this.$element[0]),f;switch(a.charAt(0)){case"n":f={top:n.top+n.height+this.options.offset,left:n.left+n.width/2-o/2};break;case"s":f={top:n.top-l-this.options.offset,left:n.left+n.width/2-o/2};break;case"e":f={top:n.top+n.height/2-l/2,left:n.left-o-this.options.offset};break;case"w":f={top:n.top+n.height/2-l/2,left:n.left+n.width+this.options.offset};break}2==a.length&&("w"==a.charAt(1)?f.left=n.left+n.width/2-15:f.left=n.left+n.width/2-o+15),s.css(f).addClass("tipsy-"+a),s.find(".tipsy-arrow")[0].className="tipsy-arrow tipsy-arrow-"+a.charAt(0),this.options.className&&s.addClass(e(this.options.className,this.$element[0])),this.options.fade?s.stop().css({opacity:0,display:"block",visibility:"visible"}).animate({opacity:this.options.opacity}):s.css({visibility:"visible",opacity:this.options.opacity})}},hide:function(){this.options.fade?this.tip().stop().fadeOut(function(){t(this).remove()}):this.tip().remove()},fixTitle:function(){var t=this.$element;(t.attr("title")||"string"!=typeof t.attr("original-title"))&&t.attr("original-title",t.attr("title")||"").removeAttr("title")},getTitle:function(){var t,e=this.$element,i=this.options;this.fixTitle();var t,i=this.options;return"string"==typeof i.title?t=e.attr("title"==i.title?"original-title":i.title):"function"==typeof i.title&&(t=i.title.call(e[0])),(t=(""+t).replace(/(^\s*|\s*$)/,""))||i.fallback},tip:function(){return this.$tip||(this.$tip=t('<div class="tipsy"></div>').html('<div class="tipsy-arrow"></div><div class="tipsy-inner"></div>'),this.$tip.data("tipsy-pointee",this.$element[0])),this.$tip},validate:function(){this.$element[0].parentNode||(this.hide(),this.$element=null,this.options=null)},enable:function(){this.enabled=!0},disable:function(){this.enabled=!1},toggleEnabled:function(){this.enabled=!this.enabled}},t.fn.tipsy=function(e){function i(i){var n=t.data(i,"tipsy");return n||(n=new s(i,t.fn.tipsy.elementOptions(i,e)),t.data(i,"tipsy",n)),n}function n(){var t=i(this);t.hoverState="in",0==e.delayIn?t.show():(t.fixTitle(),setTimeout(function(){"in"==t.hoverState&&t.show()},e.delayIn))}function o(){var t=i(this);t.hoverState="out",0==e.delayOut?t.hide():setTimeout(function(){"out"==t.hoverState&&t.hide()},e.delayOut)}if(!0===e)return this.data("tipsy");if("string"==typeof e){var l=this.data("tipsy");return l&&l[e](),this}if(e=t.extend({},t.fn.tipsy.defaults,e),e.live||this.each(function(){i(this)}),"manual"!=e.trigger){var a=e.live?"live":"bind",f="hover"==e.trigger?"mouseenter":"focus",h="hover"==e.trigger?"mouseleave":"blur";this[a](f,n)[a](h,o)}return this},t.fn.tipsy.defaults={className:null,delayIn:0,delayOut:0,fade:!1,fallback:"",gravity:"n",html:!1,live:!1,offset:0,opacity:.8,title:"title",trigger:"hover"},t.fn.tipsy.revalidate=function(){t(".tipsy").each(function(){var e=t.data(this,"tipsy-pointee");e&&i(e)||t(this).remove()})},t.fn.tipsy.elementOptions=function(e,i){return t.metadata?t.extend({},i,t(e).metadata()):i},t.fn.tipsy.autoNS=function(){return t(this).offset().top>t(document).scrollTop()+t(window).height()/2?"s":"n"},t.fn.tipsy.autoWE=function(){return t(this).offset().left>t(document).scrollLeft()+t(window).width()/2?"e":"w"},t.fn.tipsy.autoBounds=function(e,i){return function(){var s={ns:i[0],ew:i.length>1&&i[1]},n=t(document).scrollTop()+e,o=t(document).scrollLeft()+e,l=t(this);return l.offset().top<n&&(s.ns="n"),l.offset().left<o&&(s.ew="w"),t(window).width()+t(document).scrollLeft()-l.offset().left<e&&(s.ew="e"),t(window).height()+t(document).scrollTop()-l.offset().top<e&&(s.ns="s"),s.ns+(s.ew?s.ew:"")}}}(jQuery);