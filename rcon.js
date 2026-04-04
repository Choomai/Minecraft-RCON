$("input#txtCommand").keyup(function(e) {
    if(e.keyCode == 13){
        sendCommand($("#txtCommand").val());
        this.value = "";
    };
}); // Remove all from cmd input after Enter key is pressed.

$("button#btnSend").click(() => {
    if ($("#txtCommand").val() != "") $("#btnSend").prop("disabled", true);
    sendCommand($("#txtCommand").val());
    $("#txtCommand").val("");
});

$("#btnClearLog").click(() => {
    $("#groupConsole").empty();
    alertInfo("Console has cleared.");
});


// Helper function to handle checkbox state
function initCheckbox(id, defaultValue = false) {
    const stored = localStorage.getItem(id);
    const value = stored ? stored === "true" : defaultValue;
    $(`#${id}`)[0].checked = value;
    
    // Special handling for hide-desc
    if (id === "hide-desc" && value) $(".btn-xs").hide();
    
    // Set initial localStorage if not exists
    if (!stored) localStorage.setItem(id, value);
};

// Initialize checkboxes
initCheckbox("hide-desc");
initCheckbox("auto-scroll", true);

fetch("autocomplete.json").then(res => res.json())
.then(res => {
    $("#txtCommand").autocomplete({
        source: res,
        appendTo: "#txtCommandResults",
        open: () => {
            let position = $("#txtCommandResults").position(),
                left = position.left, 
                top = position.top,
                width = $("#txtCommand").width(),
                height = $("#txtCommandResults > ul").height();
            $("#txtCommandResults > ul").css({
                left: left + "px",
                top: top - height - 4 + "px",
                width: 30 + width + "px"
            });
        }
    });
})

function logMsg(msg, sep, cls){
    let date = new Date(), 
    datetime = 
        ("0" + date.getDate()).slice(-2) + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" + date.getFullYear() + " @ " +
        ("0" + date.getHours()).slice(-2) + ":" + ("0" + date.getMinutes()).slice(-2) + ":" + ("0" + date.getSeconds()).slice(-2);
    $("#groupConsole").append("<li class=\"list-group-item list-group-item-" + cls + "\"><time class=\"label label-" + cls + "\">" + datetime + "</time><strong>" + sep + "</strong> " + msg + "<div class=\"clearfix\"></div></li>");
    $("#btnSend").prop("disabled", false);
    // Clear old logs
    let logItemSize = $("#groupConsole li").length;
    if(logItemSize > 100){
        $("#groupConsole li:first").remove();
    }
    // Scroll down
    if ($("#auto-scroll").is(":checked")){
        $("#consoleContent .panel-body").scrollTop($("#groupConsole").get(0).scrollHeight);
    };
}
function logSuccess(log) {logMsg(log, "<", "success")}
function logInfo(log) {logMsg(log, "<", "info")}
function logWarning(log) {logMsg(log, "<", "warning")}
function logDanger(log) {logMsg(log, "<", "danger")}

function alertMsg(msg, cls){
    $("#alertMessage").fadeOut("slow", () => {
        $("#alertMessage").attr("class", "alert alert-"+cls);
        $("#alertMessage").html(msg);
        $("#alertMessage").fadeIn("slow");
    });
};
function alertSuccess(msg) {alertMsg(msg, "success")};
function alertInfo(msg) {alertMsg(msg, "info")};
function alertWarning(msg) {alertMsg(msg, "warning")};
function alertDanger(msg) {alertMsg(msg, "danger")};

function sendCommand(command) {
    const formData = new FormData();
    if (!command) {alertDanger("Command missing.");return;};
    formData.append("cmd", command);
    logMsg(command, ">", "success");
    fetch("api/index.php", {method: "POST", body: formData})
    .then(res => res.json())
    .then(json => {
        if (json.status == "error") {
            alertDanger(json.error); 
            logDanger(json.error);
            return;
        };

        if (json.status == "success") {
            if (json.response.indexOf("Unknown or incomplete command") != -1) {
                alertDanger("Unknown or incomplete command: " + json.command); 
                logDanger(json.response);
                return;
            }
            // else if(json.response.indexOf("Usage") != -1){
            //     alertWarning("Send success."); 
            //     logWarning(json.response);
            // }
            alertSuccess("Send success.");
            logInfo(json.response);
        };
    })
    .catch(err => {
        console.error(err);
        alertDanger("RCON error.");
        logDanger("RCON error.");
    });
};
async function saveTargetServer(host, port, passwd) {
    const formData = new FormData();
    formData.append("host", host);
    formData.append("port", port || 25575);
    formData.append("pass", passwd);
    let resultStr = null;
    try {
        const updateFetch = await fetch("api/update_target.php", {
            method: "POST",
            body: formData
        });
        resultStr = `with HTTP response code ${updateFetch.status}`
        if (updateFetch.ok) {resultStr = "Saved " + resultStr}
        else {resultStr = "Failed to save " + resultStr}
    } catch {
        resultStr = "Failed to save " + resultStr;
    } finally {
        $("span#save-db_notify").html(resultStr);
        $("span#save-db_notify").show("slide", {duration: 500});
        setTimeout(() => {$("span#save-db_notify").hide("slide", {duration: 500})}, 5000);
    }
}

function settingsPopup(type) {
    const docElem = document.documentElement;
    if (type == "show") {
        docElem.style.setProperty("--blur-amount","3px");
        docElem.style.setProperty("--alpha-amount","30%");
        $("#settings").show()
    } else {
        $("#settings").hide()
        docElem.style.setProperty("--blur-amount","0px");
        docElem.style.setProperty("--alpha-amount","0%");
    };
};
function settings(type) {
    if (type != "auto-scroll") {
        if ($("#hide-desc").is(":checked")) {$(".btn-xs").hide("fade", 800)} else {$(".btn-xs").show("fade", 800)};
    };
    localStorage.setItem(type,$("#"+type)[0].checked);
}

$("button.save-db").click(async e => {
    e.target.disabled = true;
    await saveTargetServer($('input#target_host').val(), $('input#target_port').val(), $('input#target_passwd').val());
    e.target.disabled = false;
})
$("span.settings").click(() => settingsPopup("show"))
$("span.close-popup").click(() => settingsPopup("hide"))
$("input#auto-scroll").click(() => settings("auto-scroll"))
$("input#hide-desc").click(() => settings("hide-desc"))