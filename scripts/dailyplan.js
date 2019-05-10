function additem(meal){
    console.log(meal);
    options= "<div id='add'><button>Add Receipe</button><button>Add Ingredient</button><button onclick='load_quagga()'>Scan Barcode</button><button onclick='addbarcode(\""+meal+"\")'>Add item by Barcode</button></div>";
    $( "#add" ).remove();
    
    if (meal=='b') {
        $("#breakfast").append(options);
    } else if (meal=='l') {
        $("#lunch").append(options);
    } else if (meal=='s') {
        $("#snack").append(options);
    } else if (meal=='d') {
        $("#dinner").append(options);
    }
    
}

function addbarcode(meal){
    console.log(meal);
    options= "<div id='barcode'><input type='text'><a id='add_barNo' href='additem.php'>Add item</a></div>";
    $( "#barcode" ).remove();
    if (meal=='b') {
        console.log("showing");
        $("#breakfast #add").append(options);
    } else if (meal=='l') {
        $("#lunch #add").append(options);
    } else if (meal=='s') {
        $("#snack #add").append(options);
    } else if (meal=='d') {
        $("#dinner #add").append(options);
    }
}
