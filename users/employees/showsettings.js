

function toggleUpdatePass() {
    document.getElementById('darkmodal').style.display = 'none';
    document.getElementById('updatepass').style.display = 'block';
    document.getElementById('updatepro').style.display = 'none';
}

function toggleUpdatePro() {
    document.getElementById('darkmodal').style.display = 'none';
    document.getElementById('updatepass').style.display = 'none';
    document.getElementById('updatepro').style.display = 'block';
}


function toggleDarkModal() {
    document.getElementById('darkmodal').style.display = 'block';
    document.getElementById('updatepass').style.display = 'none';
    document.getElementById('updatepro').style.display = 'none';
}