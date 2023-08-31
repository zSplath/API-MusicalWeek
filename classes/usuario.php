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
      if (mb_strlen($this->nome) < 257) {
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
      $formato = 'Y-m-d'; 

      
      $dateObj = DateTime::createFromFormat($formato, $this->dataNasc);
  
      
      if ($dateObj && $dateObj->format($formato) === $this->dataNasc) {
        return true;
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
      "INSERT INTO [dbo].[Usuario] (nome, username, data_nasc, email, senha, tipo_plano) 
      VALUES (:nome, :nick, :dataNasc, :email, :senha, 1)"
      );
      
      $insert->bindParam(':nome', $this->nome);
      $insert->bindParam(':nick', $this->nick);
      $insert->bindParam(':dataNasc', $this->dataNasc);
      $insert->bindParam(':email', $this->email);
      $insert->bindParam(':senha', $hash);

      $hash = password_hash($this->senha, PASSWORD_DEFAULT);
      
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
        if (password_verify($this->senha, $stmt->fetchColumn())) {
          return true;
        }
      }
      return false;
    }

    public function getid($conn) {
      $query = "SELECT id_usuario FROM [dbo].[Usuario] WHERE email = :email";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(":email", $this->email);
      $stmt->execute();
      $idUsuario = $stmt->fetchColumn();
      return $idUsuario;
    }

    public function select($conn, $idUsuario) {
      $query = "SELECT nome, email, username, data_nasc FROM [dbo].[Usuario] WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      if ($stmt->rowCount() == 0) {
        return false;
      }

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      $this->nome = $row['nome'];
      $this->email = $row['email'];
      $this->nick = $row['username'];
      $this->dataNasc = $row['data_nasc'];

      return true;
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
      $senha = password_hash($senha, PASSWORD_DEFAULT);

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

    public function getPlano($conn, $idUsuario) {
      $query = "SELECT tipo_plano FROM [dbo].[Usuario] WHERE id_usuario = :id";
      $stmt = $conn->prepare($query);
      $stmt->bindParam(':id', $idUsuario);
      $stmt->execute();

      if ($stmt->rowCount() == 0) {
        return false;
      }

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      return $row['tipo_plano'];
    }

    public function getEmailCensurado() {
      $email = $this->email;

      list($nome, $dominio) = explode('@', $email);
      $letra = substr($nome, 0, 1);
      $censurado = $letra . str_repeat('*', strlen($nome) - 1);
      
      return $censurado . '@' . $dominio;
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