{{-- CDN fallback when build assets fail to load (e.g. 404, blocked) --}}
<script>
(function(){
    var d=document;
    function loadCdnFallback(){
        if(window._cdnFallbackLoaded)return;
        window._cdnFallbackLoaded=true;
        var h=d.head;
        var t=d.createElement('link');t.rel='stylesheet';t.href='https://cdn.tailwindcss.com';h.appendChild(t);
        var fa=d.createElement('link');fa.rel='stylesheet';fa.href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css';fa.crossOrigin='anonymous';h.appendChild(fa);
    }
    function check(){
        var links=d.querySelectorAll('link[href*="/build/"]');
        var anyLoaded=false;
        for(var i=0;i<links.length;i++){if(links[i].sheet){anyLoaded=true;break;}}
        if(links.length>0&&!anyLoaded)loadCdnFallback();
    }
    if(d.readyState==='complete')setTimeout(check,800);
    else window.addEventListener('load',function(){setTimeout(check,800);});
})();
</script>
