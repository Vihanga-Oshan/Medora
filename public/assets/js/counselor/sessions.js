function showSection(id){
    document.querySelectorAll('.toggle-section').forEach(section =>{
        section.classList.remove('active-section')
    });

    document.getElementById(id).classList.add('active-section');

    document.querySelectorAll('.toggle-button').forEach(button =>{
        button.classList.remove('active-button')
    });

    if(id == 'sec1'){
        document.getElementById('toggle1').classList.add('active-button');
    }else if(id == 'sec2'){
        document.getElementById('toggle2').classList.add('active-button');
    }
}