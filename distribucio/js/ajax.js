// JavaScript Document

function crearXMLHttpRequest() 
{
  var xmlHttp=null;
  if (window.ActiveXObject) 
    xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
  else 
    if (window.XMLHttpRequest) 
      xmlHttp = new XMLHttpRequest();
  return xmlHttp;
}


function FAjaxParam2(url,capa,parametros,metodo){
    
	var ajax=crearXMLHttpRequest();
        var capaContenedora = document.getElementById(capa);

	//alert(formid);

	valores=parametros;	
	

    //Creamos y ejecutamos la instancia si el metodo elegido es POST
    if(metodo.toUpperCase()=='POST'){
        ajax.open ('POST', url, true);
        ajax.onreadystatechange = function() {			
            if (ajax.readyState==1) {
                capaContenedora.innerHTML="La copia de seguridad se esta realizando.<br><br><img src='http://www.escortsgoogle.es/img/borrando.gif'>";
             }else if (ajax.readyState==4){
                if(ajax.status==200){
                    document.getElementById(capa).innerHTML=ajax.responseText;
                }else if(ajax.status==404){
                    capaContenedora.innerHTML = "La direccion no existe";
                }else{
                    capaContenedora.innerHTML = "Error: "+ajax.status;
                }
            }
        }
        ajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        ajax.send(valores);
        return;
    }
    //Creamos y ejecutamos la instancia si el metodo elegido es GET
    if (metodo.toUpperCase()=='GET'){
        ajax.open ('GET', url, true);
         ajax.onreadystatechange = function() {
            if (ajax.readyState==1) {
                capaContenedora.innerHTML="La copia de seguridad se esta realizando.<br><br><img src='http://www.escortsgoogle.es/img/borrando.gif'>";
            }else if (ajax.readyState==4){
                if(ajax.status==200){
                    document.getElementById(capa).innerHTML=ajax.responseText;
                }else if(ajax.status==404){
                    capaContenedora.innerHTML = "La direccion no existe";
                }else{
                    capaContenedora.innerHTML = "Error: "+ajax.status;
                }
            }
        }
        ajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        ajax.send(null);
        return
    }
}


function FAjaxParam(url,capa,parametros,metodo){
    
	var ajax=crearXMLHttpRequest();
    var capaContenedora = document.getElementById(capa);

	//alert(formid);

	valores=parametros;	
	

    //Creamos y ejecutamos la instancia si el metodo elegido es POST
    if(metodo.toUpperCase()=='POST'){
        ajax.open ('POST', url, true);
        ajax.onreadystatechange = function() {			
            if (ajax.readyState==1) {
                capaContenedora.innerHTML="<img src='http://www.escortsgoogle.es/img/borrando.gif'>";
             }else if (ajax.readyState==4){
                if(ajax.status==200){
                    document.getElementById(capa).innerHTML=ajax.responseText;
                }else if(ajax.status==404){
                    capaContenedora.innerHTML = "La direccion no existe";
                }else{
                    capaContenedora.innerHTML = "Error: "+ajax.status;
                }
            }
        }
        ajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        ajax.send(valores);
        return;
    }
    //Creamos y ejecutamos la instancia si el metodo elegido es GET
    if (metodo.toUpperCase()=='GET'){
        ajax.open ('GET', url, true);
         ajax.onreadystatechange = function() {
            if (ajax.readyState==1) {
                capaContenedora.innerHTML="<img src='http://www.escortsgoogle.es/img/borrando.gif'>";
            }else if (ajax.readyState==4){
                if(ajax.status==200){
                    document.getElementById(capa).innerHTML=ajax.responseText;
                }else if(ajax.status==404){
                    capaContenedora.innerHTML = "La direccion no existe";
                }else{
                    capaContenedora.innerHTML = "Error: "+ajax.status;
                }
            }
        }
        ajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        ajax.send(null);
        return
    }
}

function FAjax(url,capa,formid,metodo){
    
	var ajax=crearXMLHttpRequest();
    var capaContenedora = document.getElementById(capa);

	//alert(formid);

	if(formid!=""){
		
		 var Formulario = document.getElementById(formid);
         var longitudFormulario = Formulario.elements.length;
         var valores = "";
         var sepCampos;
         sepCampos = "";
         for (var i=0; i <= Formulario.elements.length-1;i++) {
			if (Formulario.elements[i].type == "checkbox")
			{
				if (Formulario.elements[i].checked)
					valores += sepCampos+Formulario.elements[i].name+'='+encodeURI(Formulario.elements[i].value);
				else
					valores += sepCampos+Formulario.elements[i].name+'=0';
					
			}
			else
				valores += sepCampos+Formulario.elements[i].name+'='+encodeURI(Formulario.elements[i].value);
         	sepCampos="&";
		 }

	}else{
		valores=null;	
	}

    //Creamos y ejecutamos la instancia si el metodo elegido es POST
    if(metodo.toUpperCase()=='POST'){
        ajax.open ('POST', url, true);
        ajax.onreadystatechange = function() {			
            if (ajax.readyState==1) {
                capaContenedora.innerHTML="Cargando...";
             }else if (ajax.readyState==4){
                if(ajax.status==200){
                    document.getElementById(capa).innerHTML=ajax.responseText;
                }else if(ajax.status==404){
                    capaContenedora.innerHTML = "La direccion no existe";
                }else{
                    capaContenedora.innerHTML = "";
                }
            }
        }
        ajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        ajax.send(valores);
        return;
    }
    //Creamos y ejecutamos la instancia si el metodo elegido es GET
    if (metodo.toUpperCase()=='GET'){
        ajax.open ('GET', url, true);
         ajax.onreadystatechange = function() {
            if (ajax.readyState==1) {
                capaContenedora.innerHTML="Cargando...";
            }else if (ajax.readyState==4){
                if(ajax.status==200){
                    document.getElementById(capa).innerHTML=ajax.responseText;
                }else if(ajax.status==404){
                    capaContenedora.innerHTML = "La direccion no existe";
                }else{
                    capaContenedora.innerHTML = "Error: "+ajax.status;
                }
            }
        }
        ajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        ajax.send(null);
        return
    }
}


function FAjax2 (url,capa,capa2,formid,form2,metodo){
    
	var ajax=crearXMLHttpRequest();
    var capaContenedora = document.getElementById(capa);


	if(formid!=""){
		
		 var Formulario = document.getElementById(formid);
		 var Formulario2 = document.getElementById(form2);
         var longitudFormulario = Formulario.elements.length;
         var valores = "";
         var sepCampos;
         sepCampos = "";
         for (var i=0; i <= Formulario.elements.length-1;i++) {
         	valores += sepCampos+Formulario.elements[i].name+'='+encodeURI(Formulario.elements[i].value);
         	sepCampos="&";
			if (Formulario.elements[i].name.indexOf('idAnun')!=-1)
				idAnunciante = Formulario.elements[i].value;
		 }

	}else{
		valores=null;	
	}

    //Creamos y ejecutamos la instancia si el metodo elegido es POST
    if(metodo.toUpperCase()=='POST'){
        ajax.open ('POST', url, true);
        ajax.onreadystatechange = function() {			
            if (ajax.readyState==1) {
                capaContenedora.innerHTML="Cargando...";
             }else if (ajax.readyState==4){
                if(ajax.status==200){
					//alert('<a href="#" onClick="FAjax(\'activarDesactivar.php\',capa2,form2,\'post\')";><img src="/img/desactivar.jpg" alt="desactivar" width="20" border="0" title="desactivar"></a>');
					//document.getElementById(capa2).innerHTML='<a href="#" onClick="FAjax(\'activarDesactivar.php\',\''+capa2+'\',\''+form2+'\',\'post\')";><img src="/img/desactivar.jpg" alt="desactivar" width="20" border="0" title="desactivar" id="imgActInact'+idAnunciante+'"></a>&nbsp;&nbsp;';
                    document.getElementById(capa).innerHTML=ajax.responseText;
					
                }else if(ajax.status==404){
                    accionAjax();
                }else{
                    capaContenedora.innerHTML = "Error: "+ajax.status;
                }
            }
        }
        ajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        ajax.send(valores);
        return;
    }
    //Creamos y ejecutamos la instancia si el metodo elegido es GET
    if (metodo.toUpperCase()=='GET'){
        ajax.open ('GET', url, true);
         ajax.onreadystatechange = function() {
            if (ajax.readyState==1) {
                capaContenedora.innerHTML="Cargando...";
            }else if (ajax.readyState==4){
                if(ajax.status==200){
                    document.getElementById(capa).innerHTML=ajax.responseText;
                }else if(ajax.status==404){
                    capaContenedora.innerHTML = "La direccion no existe";
                }else{
                    capaContenedora.innerHTML = "Error: "+ajax.status;
                }
            }
        }
        ajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        ajax.send(null);
        return
    }
}

