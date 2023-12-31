<?php
  class Usuario {
    private $nome;
    private $nick;
    private $dataNasc;
    private $email;
    private $senha; 

    public function __construct($nome, $nick, $dataNasc, $email, $senha) {
      
      $this->nome = $nome;
      $this->nick = $nick;
      $this->dataNasc = $dataNasc;
      $this->email = $email;
      $this->senha = $senha;
    }

    public function validarNome() {
      if (mb_strlen($this->nome) < 65 && $this->nome != null) {
        return true; 
      } else {
        return false; 
      }
    }

    public function validarNick() {
      $quantidade = mb_strlen($this->nick);
      if (3 < $quantidade && $quantidade < 17) {
        return true; 
      } else {
        return false; 
      }
    }

    public function validarData() {
      $dateObj = DateTime::createFromFormat('Y-m-d', $this->dataNasc);

      if ($dateObj) {
        $hoje = new DateTime();
        $diferenca = $hoje->diff($dateObj);

        return $diferenca->y >= 18 && $diferenca->y <= 130;
      } else {
        return false;
      }
    }

    public function validarEmail() {
      $padrao = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

      if (preg_match($padrao, $this->email)) {
        return true; 
      } else {
        return false; 
      }
    }

    public function validarSenha() {
      
      $tamanhoMinimo = 8; 
      $exigeMaiuscula = true; 
      $exigeMinuscula = true; 
      $exigeNumero = true; 
      $exigeCaractereEspecial = true;
  
      if (mb_strlen($this->senha) < $tamanhoMinimo) {
        return false; 
      }
  
      $valido = true;
  
      if ($exigeMaiuscula && !preg_match('/[A-Z]/', $this->senha)) {
        $valido = false;
      }
  
      if ($exigeMinuscula && !preg_match('/[a-z]/', $this->senha)) {
        $valido = false;
      }
  
      if ($exigeNumero && !preg_match('/[0-9]/', $this->senha)) {
        $valido = false;
      }
  
      if ($exigeCaractereEspecial && !preg_match('/[^a-zA-Z0-9]/', $this->senha)) {
        $valido = false;
      }
  
      return $valido;
    }

    public function verificaEmail($conn) {
      $query = "SELECT COUNT(*) AS total_email FROM [dbo].[Usuario] WHERE email = :email";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":email", $this->email);
      $stmt->execute();
      $result = $stmt->fetchColumn();
      if (0 == $result) {
        return true;
      } else {
        return false;
      }
    }

    public function verificaNick($conn) {
      $query = "SELECT COUNT(*) AS total_user_nome FROM [dbo].[Usuario] WHERE username = :nick";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":nick", $this->nick);
      $stmt->execute();
      $result = $stmt->fetchColumn();
      if (0 == $result) {
        return true;
      } else {
        return false;
      }
    }

    public function cadastra($conn) {
      $insert = $conn->prepare(
      "INSERT INTO [dbo].[Usuario] (nome, username, data_nasc, email, senha, icon, data_cadastro, tipo_plano, status) 
      VALUES (:nome, :nick, :dataNasc, :email, :senha, 'icone0.png', dbo.datacorreta(), 0, 0)"
      );
      
      $insert->bindParam(':nome', $this->nome);
      $insert->bindParam(':nick', $this->nick);
      $insert->bindParam(':dataNasc', $this->dataNasc);
      $insert->bindParam(':email', $this->email);
      $insert->bindParam(':senha', $hash);

      $hash = hash('sha256', $this->senha);
      
      $insert->execute();
    } 

    public function cadastraGoogle($conn) {
      $insert = $conn->prepare(
      "INSERT INTO [dbo].[Usuario] (nome, username, data_nasc, email, icon, data_cadastro, tipo_plano, status) 
      VALUES (:nome, :nick, :dataNasc, :email, 'icone0.png', dbo.datacorreta(), 0, 1)"
      );
      
      $insert->bindParam(':nome', $this->nome);
      $insert->bindParam(':nick', $this->nick);
      $insert->bindParam(':dataNasc', $this->dataNasc);
      $insert->bindParam(':email', $this->email);
      
      $insert->execute();
    }

    public function cadastraSpotify($conn) {
      $insert = $conn->prepare(
      "INSERT INTO [dbo].[Usuario] (nome, username, data_nasc, email, icon, data_cadastro, tipo_plano, status) 
      VALUES (:nome, :nick, :dataNasc, :email, 'icone0.png', dbo.datacorreta(), 0, 0)"
      );
      
      $insert->bindParam(':nome', $this->nome);
      $insert->bindParam(':nick', $this->nick);
      $insert->bindParam(':dataNasc', $this->dataNasc);
      $insert->bindParam(':email', $this->email);
      
      $insert->execute();
    }

    public function login($conn) {
      $stmt = $conn->prepare("SELECT COUNT(*) AS total_email FROM [dbo].[Usuario] WHERE email = :email");
      $stmt->bindParam(":email", $this->email);
      $stmt->execute();
      if (1 == $stmt->fetchColumn()) {
        $stmt = $conn->prepare("SELECT senha FROM [dbo].[Usuario] WHERE email = :email");
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        $senha = $stmt->fetchColumn();
        
        if($senha == null) return false;
        
        if (hash_equals($senha, hash('sha256', $this->senha))) {
          return 1;
        } else {
          return 0;
        }
      } else {
        return 2;
      }
    }

    public function getid($conn) {
      $query = "SELECT id_usuario FROM [dbo].[Usuario] WHERE email = :email";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":email", $this->email);
      $stmt->execute();
      $idUsuario = $stmt->fetchColumn();
      return $idUsuario;
    }
    
    public function selectEmail($conn, $idUsuario) {
      $query = "SELECT email FROM [dbo].[Usuario] WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      if ($stmt->rowCount() == 0) {
        return false;
      }

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      $this->email = $row['email'];

      return true;
    }

    public function insertCodigo($conn, $idUsuario, $codigo) {
      
      $query = "DELETE FROM Confirmacao WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      $query = "INSERT INTO Confirmacao (id_usuario, codigo) VALUES (:id, :codigo)";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->bindParam(':codigo', $codigo);
      $stmt->execute();

      return true;
    }

    public function verificacaoEmail($conn, $idUsuario) {
      
      $query = "SELECT status FROM Usuario WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      $status = $stmt->fetchColumn();

      if ($status == 1) {
        return true;
      } else {
        return false;
      }
    }

    public function confirmaCodigo($conn, $idUsuario, $codigo) {
      
      $query = "SELECT * FROM Confirmacao WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($row['tentativas'] > 4) {
        return $row['tentativas'];
      }

      if ($row['codigo'] == $codigo) {

        $query = "UPDATE Usuario SET status = 1 WHERE id_usuario = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $idUsuario);
        $stmt->execute();

        $query = "DELETE FROM Confirmacao WHERE id_usuario = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $idUsuario);
        $stmt->execute();

        return 0;
      } else {
        $query = "UPDATE Confirmacao SET tentativas = tentativas + 1 WHERE id_usuario = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $idUsuario);
        $stmt->execute();

        return $row['tentativas'] + 1;
      }
    }

    public function insertCodigoSenha($conn, $idUsuario, $codigo) {
      
      $query = "DELETE FROM Recuperacao WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      $tempo = date('Y-m-d H:i:s');

      $query = "INSERT INTO Recuperacao (id_usuario, codigo, tempo) VALUES (:id, :codigo, :tempo)";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->bindParam(':codigo', $codigo);
      $stmt->bindParam(':tempo', $tempo);
      $stmt->execute();

      return true;
    }

    public function verificaCodigoSenha($conn, $codigo) {
      
      $query = "SELECT * FROM Recuperacao WHERE codigo = :codigo";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':codigo', $codigo);
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($row == null) {
        return 0;
      }

      $tempoLimite = new DateTime($row['tempo']);
      $tempoAtual = new DateTime();

      $tempoLimite->add(new DateInterval('P1D')); 

      if ($tempoLimite < $tempoAtual) {
        $query = "DELETE FROM Recuperacao WHERE codigo = :codigo";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();

        return -1;
      } else {
        return 1;
      } 
    }

    public function trocaSenha($conn, $codigo, $senha) {
      $senha = hash('sha256', $senha);

      $query = "SELECT id_usuario FROM Recuperacao WHERE codigo = :codigo";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':codigo', $codigo);
      $stmt->execute();

      $idUsuario = $stmt->fetchColumn();

      $query = "UPDATE Usuario SET senha = :senha WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->bindParam(':senha', $senha);
      $stmt->execute();

      $query = "DELETE FROM Recuperacao WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();
    }

    private function getEmailCensurado($email) {

      list($nome, $dominio) = explode('@', $email);
      $letra = substr($nome, 0, 1);
      $censurado = $letra . str_repeat('*', strlen($nome) - 1);
      
      return $censurado . '@' . $dominio;
    }

    public function selectLogin($conn) {
      $query = "SELECT id_usuario, username, tipo_plano FROM [dbo].[Usuario] WHERE email = :email";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":email", $this->email);
      $stmt->execute();
      
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function trocaPlano($conn, $idUsuario, $plano) {
      $query = "UPDATE Usuario SET tipo_plano = :plano WHERE id_usuario = :idUsuario";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":plano", $plano);
      $stmt->bindParam(":idUsuario", $idUsuario);
      $stmt->execute();
      
      return $plano;
    }

    public function verificaSenha($conn, $idUsuario, $senha) {
      $query = "SELECT senha FROM Usuario WHERE id_usuario = :idUsuario";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":idUsuario", $idUsuario);
      $stmt->execute();

      $hash = $stmt->fetchColumn();

      if($hash === null) return 0;

      if (hash('sha256', $senha) == $hash) {
        return 1;
      } else {
        return 2;
      }
    }

    public function novaSenha($conn, $idUsuario) {
      $hash = hash('sha256', $this->senha);

      $query = "UPDATE Usuario SET senha = :novaSenha WHERE id_usuario = :idUsuario";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":novaSenha", $hash);
      $stmt->bindParam(":idUsuario", $idUsuario);

      $stmt->execute();
    }

    public function atualiza($conn, $idUsuario, $icon) {
      $query = "UPDATE Usuario SET nome = :nome, username = :username, data_nasc = :data_nasc WHERE id_usuario = :idUsuario";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":nome", $this->nome);
      $stmt->bindParam(":username", $this->nick);
      $stmt->bindParam(":data_nasc", $this->dataNasc);
      $stmt->bindParam(":idUsuario", $idUsuario);

      $stmt->execute();
    }

    public function trocaIcone($conn, $idUsuario, $icon) {
      $stmt = $conn->prepare("SELECT icon from Usuario WHERE id_usuario = :idUsuario");
      $stmt->bindParam(":idUsuario", $idUsuario);

      $stmt->execute();

      if($stmt->fetchColumn() == $icon) return false;

      $stmt = $conn->prepare("UPDATE Usuario SET icon = :icon WHERE id_usuario = :idUsuario");
      $stmt->bindParam(":icon", $icon);
      $stmt->bindParam(":idUsuario", $idUsuario);

      $stmt->execute();

      return true;
    }
    
    public function getUsername($conn, $idUsuario) {
      $query = "SELECT username FROM Usuario WHERE id_usuario = :idUsuario";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":idUsuario", $idUsuario);
      $stmt->execute();

      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result['username'];
    }

    public function perfil($conn, $idUsuario) {
      $query = "SELECT nome, email, username, data_nasc, tipo_plano, status, icon FROM [dbo].[Usuario] WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

      $perfil['icon'] = str_replace(' ', '', $perfil['icon']);
      $perfil['email'] = $this->getEmailCensurado($perfil['email']);
      $perfil['data_nasc'] = date("d/m/Y", strtotime($perfil['data_nasc']));

      $perfil['plano'] = $perfil['tipo_plano'];
      unset($perfil['tipo_plano']);

      $perfil['nick'] = $perfil['username'];
      unset($perfil['username']); 
      
      if ($perfil['status'] == 0) {
        $perfil['confirmacao'] = false;
      } else {
        $perfil['confirmacao'] = true;
      }
      unset($perfil['status']);

      return $perfil;
    }

    public function delete($conn, $idUsuario) {
      $query = 
        "UPDATE Usuario SET 
          nome = null, 
          username = 'deletado', 
          icon = 'icone2.png', 
          data_nasc = null, 
          email = null, 
          senha = null, 
          tipo_plano = null,
          data_exclusao =  dbo.datacorreta(),
          status = 2 
        WHERE id_usuario = :id and (status <> 2 OR status IS NULL)";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      if ($stmt->rowCount() == 0) {
        return false;
      } else {
        return true;
      }
    }

    public function confirmacao($conn, $idUsuario) {
      $query = "SELECT status from Usuario where id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      if ($stmt->fetchColumn() != 0) {
        return true;
      } else {
        return false;
      }
    }

    public function getTodasSalas($conn, $idUsuario) {
      $stmt = $conn->prepare(
        "SELECT id_usuariomusicasala as id_musica_sala, id_musica, data_entrada as inicio_fila 
        from UsuarioMusicaSala where status = 0 and id_usuario = :idusuario;

        EXEC SP_RETORNA_SALAS_ATIVAS :id;
            
        SELECT s.id_sala, s.nome, u.username as nick, u.id_usuario, u.icon, 
                (
                    select case when exists 
                    (select id_sala from UsuarioMusicaSala where id_sala = s.id_sala and id_usuario = :usuario and status = 1)
                        then 1
                        else 0
                    end
                ) as participante,
                S.id_sala,
                isnull((
                    SELECT TOP 1 A.id_musica
                        FROM 
                        UsuarioMusicaSala A INNER JOIN Sala B ON A.id_sala = B.id_sala
                        WHERE 
                        dbo.datacorreta() < B.data_criacao + ordem_sala AND A.id_sala = s.id_sala
                        ORDER BY 
                        A.ordem_sala), (select top 1 id_musica from UsuarioMusicaSala where id_sala = S.id_sala order by ordem_sala desc))
                        as id_musica
                        FROM sala s
                        OUTER APPLY (
                            SELECT TOP 1 mu.id_usuario
                            FROM UsuarioMusicaSala mu
                            WHERE mu.id_sala = s.id_sala AND mu.id_musica IS NOT NULL
                        ) AS ms
                        JOIN usuario u ON ms.id_usuario = u.id_usuario
                        WHERE s.tipo_sala = 2
                        AND s.data_criacao >= DATEADD(day, -8, dbo.datacorreta()
                );

        EXEC SP_RETORNAHISTORICO :usuarioid, 1;

        EXEC SP_MINHAS_SALAS_ARTISTA :idusuarioid;
      ");
      $stmt->bindParam(':idusuario', $idUsuario);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->bindParam(':usuario', $idUsuario);
      $stmt->bindParam(':usuarioid', $idUsuario);
      $stmt->bindParam(':idusuarioid', $idUsuario);
      $stmt->execute();

      $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $stmt->nextRowset();
      $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $stmt->nextRowset();
      $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $stmt->nextRowset();

      $salasArtista = [];
      $recomendacoes = [];

      foreach ($resultado as $row) {
          $entry = [
              "id_sala_artista" => $row["id_sala"],
              "artista" => [
                  "icon" => $row["icon"],
                  "nick" => $row["nick"]
              ],
              "id_musica" => $row["id_musica"]
          ];

          if ($row["participante"] == 1) {
              if($row["id_usuario"] == $idUsuario) {
                continue;
              }
              $salasArtista[] = $entry;
          } else {
              $recomendacoes[] = $entry;
          }
      }

      $stmt->nextRowset();
      $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $stmt->nextRowset();
      $minhasSalas = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      foreach ($minhasSalas as &$row) {
        $row['ativa'] = $row['ativa'] == 1;
      }

      foreach ($salas as &$sala) {
        $sala['sala_finalizada'] = $sala['sala_finalizada'] == 1;
      }

      return [
        'filas' => $filas,
        'salas' => $salas,
        'salas_artista' => $salasArtista,
        'minhas_salas' => $minhasSalas,
        'historico' => $historico,
        'recomendacoes' => $recomendacoes
      ];
    }

    public function getNome() {
        return $this->nome;
    }

    public function getNick() {
        return $this->nick;
    }

    public function getDataNasc() {
        return $this->dataNasc;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getSenha() {
        return $this->senha;
    }
  }
?> 