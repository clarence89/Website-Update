function KeyPress(e) {
    e = e || window.event;
    var event = e;
    if (event.ctrlKey && event.altKey && event.shiftKey && event.keyCode == 112) {
        alert("Creator/Developer: Clarence Advincula Baluyot");
    }
}

document.onkeydown = KeyPress;
