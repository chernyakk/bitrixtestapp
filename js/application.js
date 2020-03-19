let nowArray = new Map();

$(document).ready()

function radio(node) {
    let sel = node.name.split(' ')[0];
    nowArray.set(sel, [node.value]);
    document.getElementById('butt1').removeAttribute("disabled");    
}

function butt1() {
    for(let item of nowArray){
        let quantity = document.getElementById(item[0] + ' [quantity]').value;
        let selector = nowArray.get(item[0]);
        nowArray.set(item[0], [selector[0], quantity]);
    }
    const result = JSON.stringify(Array.from(nowArray));
    $.post('/check.php', {result, portal}, function(data){
    });
    location.reload();
}

function reset() {
    const result = 'default';
    $.post('/check.php', {result, portal}, function(data){
        console.log(data);
    });
    location.reload();
}

function elem() {
    let oldTable = document.getElementById('bodyOfTable')
    if (oldTable) {
        oldTable.remove();
    }
    let tbody = document.createElement('tbody');
    tbody.id = 'bodyOfTable';
    let td = document.createElement('td');
    let table = document.getElementById('maintable');
    for(let item of nowArray){
        let tr = document.createElement('tr');
        let td1text = document.createTextNode(item[1]);
        let td1 = document.createElement('td');
        let td2 = document.createElement('td');
        let td3 = document.createElement('td');
        td2.innerHTML = '<input name="' + item[0] + ' [selector]" id="' + item[0] + '_words" type="radio" value="WORDS" onchange="radio(this)">'
        td2.innerHTML += '<label for="words">Слова</label><br>'
        td2.innerHTML += '<input name="' + item[0] + ' [selector]" id="' + item[0] + '_symbols" type="radio" value="SYMBOLS" onchange="radio(this)">'
        td2.innerHTML += '<label for="symbols">Символы</label>'
        td3.innerHTML = '<input type="text" size="40" id="' + item[0] + ' [quantity]" placeholder="0">'
        td1.appendChild(td1text);
        tr.appendChild(td1);
        tr.appendChild(td2);
        tr.appendChild(td3);
        tbody.appendChild(tr);
    }
    table.appendChild(tbody);            
}
