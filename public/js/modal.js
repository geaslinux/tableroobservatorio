document.addEventListener("DOMContentLoaded", () => {
    //valor = 1;
  });

$( ".opener" ).on( "click", function(e) {

    e.preventDefault();
    parametro = $(this).data('modal');
    $("<div id="+$(this).data('modal') +" title="+$(this).data('titulo')+"></div>").append($("<iframe id='iframe' onload='continuar(parametro);'></iframe>")).dialog({
        autoOpen: false,
        height: "auto",
        width: "auto",
        modal: true,
        close: function() {
            $(this).dialog("close");
            $(this).remove();            
            //location.reload();
            //form[ 0 ].reset();
            //allFields.removeClass( "ui-state-error" );
        }    
    });   

    document.querySelector('#iframe').src = $(this).data('route');
    //$( "#"+$(this).data('modal') ).dialog( "open" );


});

function continuar(parametro){

    
    $( "#"+parametro ).dialog( "open" );

    const myIframe = document.querySelector('iframe');
    const iframeWindow = myIframe.contentWindow;
    const iframeDocument = myIframe.contentDocument;
    /**************************************** */
    const body = iframeDocument.querySelector('body');
    const navbar = iframeDocument.querySelector('.navbar');
    const pie = iframeDocument.querySelector('.pie-pagina');
    body.removeChild(navbar);
    body.removeChild(pie);
    /**************************************** */
    const html = iframeDocument.querySelector('html');
    html.style.overflow = 'auto';
    /**************************************** */
    const form = iframeDocument.querySelector('form');

    const app = document.createElement("input");
    app.name = "iframe";
    app.id = "iframe";
    app.value = "iframe";
    app.type = "hidden";
    form.appendChild(app);    

}

window.addEventListener("message", recibirMensajes, false);
 

function recibirMensajes(ev)
{

    //console.log(ev.origin, typeof ev.data, ev.data);
    if(ev.data.accion == 'salir'){
        $( "#"+ev.data.modal ).dialog( "close" );
        $( "#"+ev.data.modal ).remove();

        return;        
    }
    if(ev.data.accion == 'location'){
        
        window.location.href= ev.data.url;
        return;
    }
    location.reload();

}

function volver(retorno){    
    
    if(document.querySelector('#iframe')){
        var soyTuPadre = window.parent; soyTuPadre.postMessage('reload', '*');
    }else{
        if(retorno){
            location.href = retorno;
        }else{
            history.back();
        }
    }
    
}

function salir(modal){
    var soyTuPadre = window.parent; soyTuPadre.postMessage({'accion':'salir', 'modal':modal}, '*');
}
