// Variables de uso global
var org_content = []
var contArt = 0;

window.addEventListener("load",function() {
    /*
        La función se ejecuta al entrar en la página, comprueba si hay cookies
        en caso de que si, las añade a el carrito desplegable para verlas de manera visual para usuario
        en caso de no haberlas, no hace nada.
    */
    var cContName = [];
    var cContVal = [];
    var actualCookies = decodeURIComponent(document.cookie);

    if(actualCookies.length > 0){
        actualCookies = actualCookies.split(";")
        for(var i=0;i<actualCookies.length;i++) {
            actualCookies[i] = actualCookies[i].trim();
            var tmp = actualCookies[i].split("=");
            cContName.push(tmp[0]);
            cContVal.push(tmp[1]);
        }
        CarritoAlert.startWithCookies();
        for(var n = 0; n < cContName.length; n++) {
            CarritoAlert.addProduct(cContName[n],cContVal[n]);
            contArt++;
        }
        CarritoAlert.button();
    }

    /*
        A todos los botones de la página, en este caso los que se usan para añadir productos,
        se les crea un event listener, para que ejecute una función, tomando como parámetro el numero
        de la clase de cada botón.
    */
    var botonesPag = document.getElementsByTagName("button");
    for(var i = 0; i < botonesPag.length; i++) {
        if(botonesPag[i].className != 'checkoutbtn'){
            botonesPag[i].addEventListener("click",function() {
                var num = this.className.substring(3);
                
                // Selecciona todos los elementos ej.: inp0 y guarda los valores en el array
                var elements = document.getElementsByClassName("inp"+num);
                for(var i=0; i<elements.length; i++) {
                    org_content.push(elements[i].value);
                }

                if(org_content[2] > 0) {
                    // Realiza un post al index con los valores que se guardaran como cookies
                    $.ajax ({
                        type: 'POST',
                        url: 'index.php',
                        data: {
                            n: org_content[0],
                            v: org_content[2]
                        },
                    });

                    /* Borramos el contenido de la tabla antes de añadir */
                    document.getElementsByClassName("errorCarrito").remove();
        
                    /*
                        Lo que hace es que cada vez que se añade algo al carrito, muestra en el desplegable
                        el nuevo producto insertado, con su respectivo nombre y cantidad añadida
                    */
                    var elemtit = document.getElementsByClassName("removeTitle");
                    if(elemtit.length < 1) {CarritoAlert.title();}
                    var elem = document.getElementsByClassName("chout");
                    if(elem.length >= 1) {document.getElementsByClassName("chout").remove();}
                    if(obtenerCookie(org_content[0]) == "") {
                        CarritoAlert.addProduct(org_content[0],org_content[2]);
                        contArt++;
                    } else {
                        document.cookie = org_content[0]+"="+org_content[2];
                        CarritoAlert.removeRepitedProduct(org_content[0]);
                        CarritoAlert.addProduct(org_content[0],org_content[2]);
                    }
                    CarritoAlert.button();
                }
                org_content = [];
            },false);
        }
    }
},false);

var CarritoAlert = new function() {
    /*
        Esta función lo que hace es insertar un nuevo producto dentro
        del carrito de productos, para visualizar lo que tiene de manera que lo vea el usuario
    */
    this.addProduct = function(nombre, valor){
        var trPadre = document.createElement("TR");
        trPadre.setAttribute("class",nombre);
        var thProd = document.createElement("TD");
        var t = document.createTextNode(nombre);
        thProd.appendChild(t);
        trPadre.appendChild(thProd);
        var thCant = document.createElement("TD");
        var z = document.createTextNode(valor);
        thCant.appendChild(z);
        trPadre.appendChild(thCant);
        var thCant = document.createElement("TD");
        var tdImg = document.createElement("IMG");
        tdImg.setAttribute("src", "img/delete.png");
        tdImg.setAttribute("class", "cancelbtn");
        tdImg.setAttribute("onclick", "CarritoAlert.removeProduct('"+nombre+"')");
        thCant.appendChild(tdImg);
        trPadre.appendChild(thCant);
        document.getElementById("contenidoCarrito").appendChild(trPadre);
    } 

    /*
        Función encargada de establecer el titulo dentro del carrito, unicamente
        añade a la tabla los titulos Producto y Cantidad
    */
    this.title = function() {
        var trPadre = document.createElement("TR");
        trPadre.setAttribute("class", "removeTitle");
        var thProd = document.createElement("TH");
        var t = document.createTextNode("Producto");
        thProd.appendChild(t);
        trPadre.appendChild(thProd);
        var thCant = document.createElement("TH");
        var z = document.createTextNode("Cantidad");
        thCant.appendChild(z);
        trPadre.appendChild(thCant);
        var thCancel = document.createElement("TH");
        var x = document.createTextNode("");
        thCancel.appendChild(x);
        trPadre.appendChild(thCancel);
        document.getElementById("contenidoCarrito").appendChild(trPadre);
    }

    /*
        Función que inserta dentro de la tabla un botón para ir al checkout de la compra,
        en caso de que el usuario no sepa que clicando en la imagen va directamente.
    */
    this.button = function() {
        var trPadre = document.createElement("TR");
        trPadre.setAttribute("class", "chout");
        var tdProd = document.createElement("TD");
        tdProd.setAttribute("colspan", "3");
        tdProd.setAttribute("class", "btncontainer");
        var formPadre = document.createElement("FORM");
        formPadre.setAttribute("action", "carrito.php");
        var btnPadre = document.createElement("BUTTON");
        btnPadre.setAttribute("type", "submit");
        btnPadre.setAttribute("class", "checkoutbtn");
        var t = document.createTextNode("Procesar Pedido");
        btnPadre.appendChild(t);
        formPadre.appendChild(btnPadre);
        tdProd.appendChild(formPadre);
        trPadre.appendChild(tdProd);
        document.getElementById("contenidoCarrito").appendChild(trPadre);
    }

    /*
        Función que se ejecuta cuando se pulsa la X dentro del carrito en cualquiera de los productos,
        elimina dicho producto de manera visual y su respectiva cookie, y en caso de que ya no haya más
        productos, lo indica claramente en el carrito.
    */
    this.removeProduct = function(nombre) {
        document.getElementsByClassName(nombre).remove();
        borrarCookie(nombre);
        borradoCorrectamente(nombre);
        contArt--;
        if(contArt == 0){
            CarritoAlert.carritoEmpty();
            document.getElementsByClassName("removeTitle").remove();
            document.getElementsByClassName("chout").remove();
            titulos = true;
        }
    }

    /* 
        Función que se ejecuta cuando se quiere añadir un producto que ya esta en el carrito,
        simplemente borra el producto anterior y añade el nuevo con el nuevo valors
    */
    this.removeRepitedProduct = function(nombre) {
        document.getElementsByClassName(nombre).remove();
        document.getElementsByClassName("chout").remove();
    }

    /*
        Función que se ejecuta en caso de que se inicie la página y existan cookies,
        unicamente crea el desplegable del carrito.
    */
    this.startWithCookies = function() {
        document.getElementsByClassName("errorCarrito").remove();
        CarritoAlert.title();
    }

    /*
        Función que se ejecuta en caso de que el carrito no tenga más productos,
        simplemente indica en el desplegable, que no hay nada.
    */
    this.carritoEmpty = function() {
        var trPadre = document.createElement("TR");
        trPadre.setAttribute("class", "errorCarrito");
        var thCant = document.createElement("TH");
        var tdImg = document.createElement("IMG");
        tdImg.setAttribute("src", "img/error.gif");
        tdImg.setAttribute("class", "notfound");
        thCant.appendChild(tdImg);
        trPadre.appendChild(thCant);
        document.getElementById("contenidoCarrito").appendChild(trPadre);

        var trPadre2 = document.createElement("TR");
        trPadre2.setAttribute("class", "errorCarrito");
        var thCant2 = document.createElement("TD");
        var x = document.createTextNode("El carrito esta vacío.");
        thCant2.appendChild(x);
        trPadre2.appendChild(thCant2);
        document.getElementById("contenidoCarrito").appendChild(trPadre2);
    }
}

// Borra la cookie que se le pasa como parámetro
function borrarCookie(name) {
    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

// Devuelve el valor de la cookie indicada
function obtenerCookie(clave) {
    var name = clave + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}

/*
    Se encarga de mostrar y de borrar el mensaje de alerta que se le muestra
    al usuario al eliminar un producto del desplegable del carrito.
*/
function borradoCorrectamente(nombre) {
    document.getElementsByClassName("hover_bkgr_fricc")[0].style.display = "block";
    document.getElementById("popText").innerHTML = "Se ha eliminado el producto \""+nombre+"\" satisfactoriamente.";
    setTimeout(function(){ document.getElementsByClassName("hover_bkgr_fricc")[0].style.display = "none"; }, 3000);
}

function closeAlertRemove() {
    document.getElementsByClassName("hover_bkgr_fricc")[0].style.display = "none";
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