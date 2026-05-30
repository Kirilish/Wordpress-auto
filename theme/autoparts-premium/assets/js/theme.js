(function(){
  document.documentElement.classList.add('js');
  const reduce = document.body.classList.contains('reduce-motion');
  if(!reduce && 'IntersectionObserver' in window){
    const io = new IntersectionObserver(entries=>entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('is-visible'); io.unobserve(e.target); }}),{threshold:.12});
    document.querySelectorAll('.benefits article,.how article,.ap-part-card,.trust').forEach(el=>io.observe(el));
  }
})();
