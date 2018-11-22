
/* 
    Función que se ejecuta al cargar la página.
*/
window.addEventListener("load",function() {
    this.setTimeout(cargarContenido,100);
    /* 
        Establece el funcionamiento del enlace borrar, que se encuentra
        al lado de el nombre de cada producto, simplemente lo borra de la tabla
        y borra la cookie asociada.
    */
    var botonesborrar = document.getElementsByClassName("borrarproducto");
    var contadorProductos = botonesborrar.length;
    for(var i = 0; i < contadorProductos; i++){
        botonesborrar[i].addEventListener("click",function(e) {
            document.cookie = e.target.id + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            var valorunidad = parseFloat(document.getElementById("precio"+e.target.id).textContent.slice(0, -1));
            var costetotal = parseFloat(document.getElementById("costetotal").textContent.slice(0, -1));
            document.getElementById("costetotal").innerHTML = Math.round((costetotal-valorunidad) * 100) / 100 + '€';
            document.getElementById(e.target.id).remove();
            contadorProductos--;
            if(contadorProductos == 0) {
                ceroProductos();
            }
        },false);
    }

    this.document.getElementById("finalizarcompra").addEventListener("click",function() {
        if(parseInt(document.getElementById("costetotal").textContent.slice(0, -1)) != 0) {
            document.getElementsByClassName("dniinput")[0].style.display = "block";
            document.getElementById("userdni").focus();
        }
    },false);

    this.document.getElementById("procesarpedido").addEventListener("click",function() {
        if(document.getElementById('userdni').value.length == 9) {
            var myCookies = getCookies();
            $.redirect('factura.php', { json_string:JSON.stringify(myCookies), dni:document.getElementById('userdni').value });
        } else {
            document.getElementById('userdni').style.border = "3px solid red";
        }
    },false);
    
},false);

var getCookies = function(){
    var pairs = document.cookie.split(";");
    var cookies = {};
    for (var i=0; i<pairs.length; i++){
        var pair = pairs[i].split("=");
        cookies[(pair[0]+'').trim()] = unescape(pair[1]);
    }
    return cookies;
}

function cargarContenido() {
    /*
        Función que elimina la pantalla del loader, y muestra el contendio
        del carrito, modifica css del loader también.
    */
    var objetosloader = document.getElementsByClassName("loadercontent");
    for(var i = 0; i < objetosloader.length; i++) {
        objetosloader[i].style.display = "none";
    }
    var objetosmain = document.getElementsByClassName("contenidomain");
    for(var i = 0; i < objetosmain.length; i++) {
        objetosmain[i].style.display = "block";
    }
    document.getElementsByTagName("body")[0].classList.remove("bodycssloader");
}

function ceroProductos() {
    /*
        Borra la tabla de productos a su versión sin productos, 
        simplemente borra ciertas celdas y crea otras.
    */
    document.getElementsByClassName("finalinfo")[0].remove();
    var x = document.createElement("TR");
    var z = document.createElement("TD");
    z.setAttribute("colspan", "3");
    z.appendChild(document.createTextNode("No se encuentran productos."));
    x.appendChild(z);
    document.getElementById("cesta").appendChild(x);

    var c = document.createElement("TR");
    c.setAttribute("class", "finalinfo");
    var v = document.createElement("TD");
    v.appendChild(document.createTextNode("Precio total"));
    var b = document.createElement("TD");
    b.appendChild(document.createTextNode("0€"));
    var n = document.createElement("TD");
    n.setAttribute("class", "btncontainer");
    var m = document.createElement("BUTTON");
    m.setAttribute("id","finalizarcompra");
    m.appendChild(document.createTextNode("Finalizar Compra"));
    n.appendChild(m);
    c.appendChild(v);
    c.appendChild(b);
    c.appendChild(n);
    document.getElementById("cesta").appendChild(c);

    document.getElementsByClassName("dniinput")[0].style.display = "none";

}

/* El unico uso de este código es la sustitución de elementos de la tabla */

Element.prototype.remove = function() {
    this.parentElement.removeChild(this);
}
NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
    for(var i = this.length - 1; i >= 0; i--) {
        if(this[i] && this[i].parentElement) {
            this[i].parentElement.removeChild(this[i]);
        }
    }
}