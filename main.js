// 设置有效期

ageCon.onclick = function () {
    if (ageBox.checked == true) {
        dayBox.removeAttribute("readonly");
    }
    else {
        dayBox.readonly = "readonly";
        dayBox.setAttribute("readonly", true, 0);
    }
}

// 提前确认和开启cover
function checkBefore() {
    if (linkBox.value == null) {
        linkBox.value = "";
        linkBox.setAttribute("placeholder", "请输入网址", 0);
    }
    else {
        waiting.setAttribute("open", "true", 0);
        cover.setAttribute("open", "true", 0);
    }
}

// 展示结果
function showResult() {
    waiting.setAttribute("open", "false", 0);
    result = JSON.parse(resultBox.document.body.innerHTML);
    if (result.code >= 0)
        resultTip.innerHTML = "缩短成功";
    else
        resultTip.innerHTML = "缩短失败 (Error Code: " + result.code + ")";
    resultInput.value = result.msg;
    resultMsg.setAttribute("open", "true", 0);
}

// 监听iframe
iframe = document.getElementById("resultBox");
if (iframe.attachEvent) {
    iframe.attachEvent("onload", function () {
        showResult();
    });
} else {
    iframe.onload = function () {
        showResult();
    };
}

// 复制
copyButton.onclick = function () {
    content = result.msg;
    var aux = document.createElement("input");
    aux.setAttribute("value", content);
    document.body.appendChild(aux);
    aux.select();
    document.execCommand("copy");
    document.body.removeChild(aux);
}

closeButton.onclick = function () {
    cover.setAttribute("open", "false", 0);
    resultMsg.setAttribute("open", "false", 0);
}
