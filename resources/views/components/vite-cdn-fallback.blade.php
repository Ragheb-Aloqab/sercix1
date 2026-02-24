{{-- CDN fallback when build assets fail - loads full styling from CDN --}}
<script>
(function(){
    var d=document;
    function loadCdnFallback(){
        if(window._cdnFallbackLoaded)return;
        window._cdnFallbackLoaded=true;
        var h=d.head;
        var fa=d.createElement('link');fa.rel='stylesheet';fa.href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css';fa.crossOrigin='anonymous';h.appendChild(fa);
        var fonts=d.createElement('link');fonts.rel='stylesheet';fonts.href='https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Tajawal:wght@400;500;700;800&family=Figtree:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap';h.appendChild(fonts);
        var fallback=d.createElement('link');fallback.rel='stylesheet';fallback.href='{{ asset("css/fallback.css") }}';h.appendChild(fallback);
        var tw=d.createElement('script');tw.src='https://cdn.tailwindcss.com';h.appendChild(tw);
        tw.onload=function(){
            var cfg=d.createElement('script');
            cfg.textContent="tailwind.config={darkMode:'class',theme:{extend:{colors:{servx:{black:'#0B0B0D','black-soft':'#111111','black-card':'#151515',red:'#DC2626','red-hover':'#EF4444',silver:'#B8B8B8','silver-light':'#E5E5E5'}},fontFamily:{servx:['Rajdhani','Tajawal','system-ui','sans-serif']},boxShadow:{soft:'0 4px 24px rgba(0,0,0,0.4)','servx-card':'0 8px 32px rgba(0,0,0,0.5)'}}}}";
            h.appendChild(cfg);
        };
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
