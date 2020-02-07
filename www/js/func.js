
dom = (document.getElementById)? true:false
ns4 = (document.layers)? true:false
ie4 = (document.all && !dom)? true:false

function t_editItem(item_id) {
    document.getElementById("label"+item_id).style.display="block";
    document.getElementById("input"+item_id).style.display="none";
}