<?php

    if(!isset($vars['email']) || !isset($vars['senha'])) {
        $response = array();
        $resposta['descricao'] = "";
        if (!isset($vars['email'])) {
            $resposta['email'] = null;
            $resposta['descricao'] .= "Email não enviado. ";
        }
        if (!isset($vars['senha'])) {
            $resposta['senha'] = null;
            $resposta['descricao'] .= "Senha não enviado. ";
        }
        http_response_code(400);
        echo json_encode($response);
        exit();
    }

    include("../../db/dbconexao.php");
    include("../../classes/usuario.php");
    include("../../token/gera/token.php");

    use Firebase\JWT\JWT;

    $usuario = new Usuario('','','',$vars['email'],$vars['senha']);

    try {
        $codigo = $usuario->login($conn);

        if ($codigo == 1) {
            $select = $usuario->selectLogin($conn);
            http_response_code(200);
            echo json_encode(
                array(
                    'token' => gerarToken($select['id_usuario']),
                    'nick' => $select['username'],
                    'plano' => $select['tipo_plano']
                )
            );
            exit();
        }  elseif ($codigo == 2) {
            $select = $usuario->selectLogin($conn);
            http_response_code(404);
            echo json_encode(
                array(
                    'login'=> false,
                    'email' => false,
                    'descricao' => "Email não cadastrado"
                )
            );
            exit();
        } else {
            http_response_code(401);
            echo json_encode(
                array(
                    'login'=> false,
                    'senha' => false,
                    'descricao' => "Senha incorreta"
                )
            );
            exit();
        }
    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }
?>