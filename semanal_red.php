<?php
//Desenvolvido por WHMCS.RED
//Versão 0.1
//Laravel DataBase
use WHMCS\Database\Capsule;
//Bloqueio de Acesso direto ao arquivo
if(!defined("WHMCS")){
	die("Acesso restrito!");
}
//Função de próximo dia util feita por: http://blog.thiagobelem.net/calculando-o-proximo-dia-util-de-uma-data
function proximoDiaUtil($data, $saida = 'Y-m-d') {
    //Pegando Ano
    $ano = date('Y');
    //Lista os feriados nascionais
    $feriados = array(''.$ano.'-01-01', ''.$ano.'-02-27', ''.$ano.'-02-28', ''.$ano.'-04-14', ''.$ano.'-04-16', ''.$ano.'-04-21', ''.$ano.'-05-01', ''.$ano.'-06-15', ''.$ano.'-09-07', ''.$ano.'-10-12', ''.$ano.'-11-02', ''.$ano.'-11-15', ''.$ano.'-12-25');
	// Converte $data em um UNIX TIMESTAMP
	$timestamp = strtotime($data);
	// Calcula qual o dia da semana de $data
	// O resultado será um valor numérico:
	// 1 -> Segunda ... 7 -> Domingo
	$dia = date('N', $timestamp);
	
	// Se for sábado (6), domingo (7), calcula a próximo dia útil
    if($dia >= 6){
        $timestamp_final = $timestamp + ((8 - $dia) * 3600 * 24);
    }
    // Não é sábado nem domingo, mantém a data de entrada
    else{
        $timestamp_final = $timestamp;
    }
    return date($saida, $timestamp_final);
}
function semanal_red($vars){
    //capturando o ID da fatura
    $id_invoice = $vars['invoiceid'];
    //Pega o vencimento da fatura
    foreach(Capsule::table('tblinvoices')->WHERE('id', $id_invoice)->get() as $invoicestbl){
        $vencimentofatura = $invoicestbl->duedate;
    }
    //faz a verificação e a correção caso o vencimento seja em final de semana
    $verificao_vencimento = proximoDiaUtil($vencimentofatura);
    //Verifica se são diferentes, caso sim ele ira salvar a informação
    if($vencimento_fatura!=$verificao_vencimento){
        //faz update no banco de dados
        Capsule::table('tblinvoices')->WHERE('id', $id_invoice)->update(['duedate' => $verificao_vencimento]);
        logActivity('[SEMANAL RED] A fatura N°'.$id_invoice.' foi alterada de vencimento pois o mesmo caia em um final de semana.');
    }
}
//Adicionando o hook
add_hook('InvoiceCreationPreEmail',1,'semanal_red');
?>