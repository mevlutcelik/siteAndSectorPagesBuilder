// Kontrolü geçemeyen input sayısı
let errInput = [];

function addReferances() {
    let referances = document.querySelector('#referances');
    let refInputHidden = document.querySelector('input[name="referance-input-num"]');
    let refInputNum = referances.childElementCount / 2;
    ++refInputNum;
    refInputHidden.value = refInputNum;
    let newInputName = document.createElement('input');
    newInputName.type = 'text';
    newInputName.name = `referance-name-${refInputNum}`;
    newInputName.id = `referance-name-${refInputNum}`;
    newInputName.placeholder = `${refInputNum}. Referans adı`;
    newInputName.style.marginBottom = '0.5rem';
    let newInputLink = document.createElement('input');
    newInputLink.type = 'text';
    newInputLink.name = `referance-link-${refInputNum}`;
    newInputLink.id = `referance-link-${refInputNum}`;
    newInputLink.placeholder = `${refInputNum}. Referans linki`;
    newInputLink.style.marginBottom = '2rem';
    referances.append(newInputName);
    referances.append(newInputLink);
}

// Zorunlu değişkeni inputa ekleme fonksiyonu
function addVariable(id, variable) {
    let input = document.querySelector(`#${id}`);
    input.value = document.querySelector(`#${id}`).value + variable;
    controleInput(input);
}

// Hatalı inputları diziye ekleyen fonksiyon
function pushErr(input) {
    if (errInput.find(arr => arr.id === input.getAttribute('id')) === undefined) {
        errInput.push({
            'id': input.getAttribute('id')
        });
    }
}


// Hatasız inputları diziden silen fonksiyon
function deleteErr(input) {
    if (errInput.find(arr => arr.id === input.getAttribute('id')) !== undefined) {
        errInput.splice(errInput.findIndex(arr => arr.id === input.getAttribute('id')), 1);
    }
}

// Mesaj Fonksiyonu
function msg(text, input, type = 'error') {
    let parent = input.parentElement;
    if (parent.querySelector(`label[message="true"]`) === null) {
        let msgDiv = document.createElement('label');
        msgDiv.setAttribute('for', input.getAttribute('id'));
        msgDiv.setAttribute('message', 'true');
        msgDiv.classList.add('message', type);
        msgDiv.innerHTML = text;
        parent.appendChild(msgDiv);
    } else {
        parent.querySelector(`label[message="true"]`).innerHTML = text;
    }
    input.classList.add('invalid');
    pushErr(input);
}

function removeMsg(input) {
    if (input.parentElement.querySelector(`label[message="true"]`) !== null) {
        input.parentElement.querySelector(`label[message="true"]`).innerHTML = null;
    }
    input.classList.remove('invalid');
    deleteErr(input);
}

// Inputu kontrol eden fonksiyon
function controleInput(input, write = true) {

    if (input.getAttribute('required') === '') {

        // Karakter kontrolü aktifse
        if (input.getAttribute('controle-char') === 'true') {
            let id = input.getAttribute('id');
            let limit = id.indexOf('title') !== -1 ? 60 : 140;
            let variable = id.indexOf('home') !== -1 ? '{siteName}' : '{sectorName}';
            let remainingNum = limit - input.value.trim().length;
            document.querySelector(`#${id}-char`).innerHTML = remainingNum;
            if (input.value.trim().length !== 0) {
                if (input.value.trim().length > limit) {
                    msg('Lütfen karakter limitini geçmeyin!', input);
                } else {
                    if (input.value.trim().indexOf(variable) === -1) {
                        msg('Lütfen zorunlu değişkeni kullanın!', input);
                    } else {
                        removeMsg(input);
                    }
                }
            } else {
                if (write) {
                    msg('Lütfen boş bırakmayın!', input);
                }
            }
        } else {
            if (input.value.trim().length !== 0) {
                if (input.type === 'text') {
                    if (input.value.trim().length === 0) {
                        msg('Lütfen boş bırakmayın!', input);
                    } else {
                        removeMsg(input);
                    }
                } else if (input.type === 'tel') {
                    if (input.value.length === 0) {
                        msg('Lütfen boş bırakmayın!', input);
                    } else {
                        let result = input.value.trim().match(/[a-zA-Z-_]/gi);
                        if (result !== null || input.value.trim().length !== 10) {
                            msg('Lütfen doğru bir telefon giriniz!', input);
                        } else {
                            removeMsg(input);
                        }
                    }
                }
            } else {
                if (input.type !== 'file') {
                    if (write) {
                        msg('Lütfen boş bırakmayın!', input);
                    }
                }
            }
        }
        if (!write) {
            if (input.type !== 'file') {
                pushErr(input);
            }
        }
        if (errInput.length === 0) {
            document.querySelector('form').querySelector('button').removeAttribute('disabled');
        } else {
            document.querySelector('form').querySelector('button').setAttribute('disabled', 'true');
        }
    }

}

// Pencere yüklendikten sonra yapılan işlemler
window.addEventListener('DOMContentLoaded', function () {
    let inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        controleInput(input, false);
        input.addEventListener('keyup', function () {
            controleInput(input);
        });
    });
});