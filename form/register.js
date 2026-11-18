document.querySelector('.button').onclick = function() {
    let password = document.querySelector('#password').value,
    confirmPassword = document.querySelector('#confirm-password').value;

    if(password != confirmPassword) {
        alert("Password must match. Please try again")
        return false
    }
}

document.getElementById('uploadCOR').addEventListener('change', function() {
    const fileName = this.files[0]?.name || 'Upload COR';
    document.getElementById('file-label').textContent = fileName;
});

const form = document.querySelector(".reg-form form"),
continueBtn = form.querySelector(".button");

form.onsubmit = ()=> {
    e.preventDefault();
}

continueBtn.onclick = ()=> {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "reg.php", true);
    xhr.onload = ()=> {
        if(xhr.readyState === XMLHttpRequest.DONE) {
            if(xhr.status === 200) {
                let data = xhr.response;
                if (data === "success") {
                    location.href = "Templates/template.php ";   
                }
            }
        }
    }

    let formData = new formData(form);

    xhr.send(formData);
}