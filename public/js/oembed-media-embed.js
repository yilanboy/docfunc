(()=>{"use strict";({507:function(){var t=this&&this.__awaiter||function(t,e,n,r){return new(n||(n=Promise))((function(o,i){function c(t){try{u(r.next(t))}catch(t){i(t)}}function a(t){try{u(r.throw(t))}catch(t){i(t)}}function u(t){var e;t.done?o(t.value):(e=t.value,e instanceof n?e:new n((function(t){t(e)}))).then(c,a)}u((r=r.apply(t,e||[])).next())}))},e=this&&this.__generator||function(t,e){var n,r,o,i,c={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:a(0),throw:a(1),return:a(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function a(i){return function(a){return function(i){if(n)throw new TypeError("Generator is already executing.");for(;c;)try{if(n=1,r&&(o=2&i[0]?r.return:i[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,i[1])).done)return o;switch(r=0,o&&(i=[2&i[0],o.value]),i[0]){case 0:case 1:o=i;break;case 4:return c.label++,{value:i[1],done:!1};case 5:c.label++,r=i[1],i=[0];continue;case 7:i=c.ops.pop(),c.trys.pop();continue;default:if(!(o=c.trys,(o=o.length>0&&o[o.length-1])||6!==i[0]&&2!==i[0])){c=0;continue}if(3===i[0]&&(!o||i[1]>o[0]&&i[1]<o[3])){c.label=i[1];break}if(6===i[0]&&c.label<o[1]){c.label=o[1],o=i;break}if(o&&c.label<o[2]){c.label=o[2],c.ops.push(i);break}o[2]&&c.ops.pop(),c.trys.pop();continue}i=e.call(t,c)}catch(t){i=[6,t],r=0}finally{n=o=0}if(5&i[0])throw i[1];return{value:i[0]?i[1]:void 0,done:!0}}([i,a])}}},n=/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=)?(.+)/g,r=/(?:https?:\/\/)?(?:www\.)?twitter\.com\/(?:[^\/]+\/)+status(?:es)?\/(\d+)/g;document.querySelectorAll("figure.media > oembed").forEach((function(o){var i,c,a=o.getAttribute("url");null!==a&&(c=o,(i=a).match(n)?function(n,r){return t(this,void 0,void 0,(function(){var t,o;return e(this,(function(e){switch(e.label){case 0:return t={url:n},[4,fetch("/api/oembed/youtube",{method:"POST",body:JSON.stringify(t),headers:{Accept:"application/json","Content-Type":"application/json"}})];case 1:return[4,e.sent().json()];case 2:return o=e.sent(),r.insertAdjacentHTML("afterend",o.html),[2]}}))}))}(i,c).catch((function(t){return console.error(t)})):i.match(r)&&function(n,r){return t(this,void 0,void 0,(function(){var t,o;return e(this,(function(e){switch(e.label){case 0:return t={url:n},[4,fetch("/api/oembed/twitter",{method:"POST",body:JSON.stringify(t),headers:{Accept:"application/json","Content-Type":"application/json"}})];case 1:return[4,e.sent().json()];case 2:return o=e.sent(),r.insertAdjacentHTML("afterend",o.html),window.twttr.widgets.load(document.getElementById("blog-post")),[2]}}))}))}(i,c).catch((function(t){return console.error(t)})))}))}})[507]()})();