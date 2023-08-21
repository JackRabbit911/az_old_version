const uri = '/_profiler' + window.location.pathname + window.location.search;
let body = document.querySelector("body");

function addProfiler() {
    fetch(uri).then(function(response) {
        response.text().then(function(text) {
            body.insertAdjacentHTML('afterend', text);
        });
    });
}

function resizeBody() {
    // let h = document.body.offsetHeight - 30;
    let h = body.offsetHeight - 30;
    document.body.style.setProperty('height', h + "px", 'important');
}

window.addEventListener('resize', () => {
	resizeBody();
});

window.addEventListener('load', () => {
	resizeBody();
    addProfiler();
});
