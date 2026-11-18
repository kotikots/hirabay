function loginAlert() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('error') === '1') {
      alert('Incorrect Email or Password');
    }
};

const form = document.querySelector(".login-form form"),
continueBtn = form.querySelector(".button");

form.onsubmit = ()=> {
    e.preventDefault();
}

continueBtn.onclick = ()=> {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "log.php", true);
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

function loginAlert() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('error') === '1') {
      alert('Incorrect Email or Password');
    }
  };

  function showPassword() {
      let password = document.getElementById("password");
      if(password.type === "password") {
          password.type = "text";
      } else {
          password.type = "password";
      }
  }        