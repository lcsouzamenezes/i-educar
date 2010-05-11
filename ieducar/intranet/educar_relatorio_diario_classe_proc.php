<?php

/**
 * i-Educar - Sistema de gest�o escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itaja�
 *                     <ctima@itajai.sc.gov.br>
 *
 * Este programa � software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * sob os termos da Licen�a P�blica Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a vers�o 2 da Licen�a, como (a seu crit�rio)
 * qualquer vers�o posterior.
 *
 * Este programa � distribu��do na expectativa de que seja �til, por�m, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia impl��cita de COMERCIABILIDADE OU
 * ADEQUA��O A UMA FINALIDADE ESPEC�FICA. Consulte a Licen�a P�blica Geral
 * do GNU para mais detalhes.
 *
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral do GNU junto
 * com este programa; se n�o, escreva para a Free Software Foundation, Inc., no
 * endere�o 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Arquivo dispon�vel desde a vers�o 1.0.0
 * @version   $Id$
 */

require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';
require_once 'include/clsPDF.inc.php';

/**
 * clsIndexBase class.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class clsIndexBase extends clsBase
{
  function Formular()
  {
    $this->SetTitulo($this->_instituicao . ' i-Educar - Di�rio de Classe');
    $this->processoAp = 664;
    $this->renderMenu = FALSE;
    $this->renderMenuSuspenso = FALSE;
  }
}

/**
 * indice class.
 *
 * @author    Prefeitura Municipal de Itaja� <ctima@itajai.sc.gov.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   iEd_Pmieducar
 * @since     Classe dispon�vel desde a vers�o 1.0.0
 * @version   @@package_version@@
 */
class indice extends clsCadastro
{
  var $pessoa_logada;

  var $ref_cod_instituicao;
  var $ref_cod_escola;
  var $ref_cod_serie;
  var $ref_cod_turma;

  var $ano;
  var $mes;
  var $mes_inicial;
  var $mes_final;

  var $nm_escola;
  var $nm_instituicao;
  var $ref_cod_curso;
  var $sequencial;
  var $pdf;
  var $pagina_atual = 1;
  var $total_paginas = 1;
  var $nm_professor;
  var $nm_turma;
  var $nm_serie;
  var $nm_disciplina;

  var $numero_registros;
  var $em_branco;

  var $page_y = 125;

  var $get_file;

  var $cursos = array();

  var $get_link;

  var $total;

  var $ref_cod_modulo;
  var $data_ini, $data_fim;

  var $z = 0;

  var $meses_do_ano = array(
    1  => 'JANEIRO',
    2  => 'FEVEREIRO',
    3  => 'MAR�O',
    4  => 'ABRIL',
    5  => 'MAIO',
    6  => 'JUNHO',
    7  => 'JULHO',
    8  => 'AGOSTO',
    9  => 'SETEMBRO',
    10 => 'OUTUBRO',
    11 => 'NOVEMBRO',
    12 => 'DEZEMBRO'
  );

  function renderHTML()
  {
    if ($_POST) {
      foreach ($_POST as $key => $value) {
        $this->$key = $value;
      }
    }

    if ($this->ref_ref_cod_serie) {
      $this->ref_cod_serie = $this->ref_ref_cod_serie;
    }

    $fonte    = 'arial';
    $corTexto = '#000000';

    if (empty($this->ref_cod_turma)) {
      echo '
        <script>
          alert("Erro ao gerar relat�rio!\nNenhuma turma selecionada!");
          window.parent.fechaExpansivel(\'div_dinamico_\'+(window.parent.DOM_divs.length-1));
        </script>';

      return TRUE;
    }

    $modulo_sequencial    = explode('-', $this->ref_cod_modulo);
    $this->ref_cod_modulo = $modulo_sequencial[0];
    $this->sequencial     = $modulo_sequencial[1];

    if ($this->ref_cod_escola) {
      $obj_escola = new clsPmieducarEscola($this->ref_cod_escola);
      $det_escola = $obj_escola->detalhe();
      $this->nm_escola = $det_escola['nome'];

      $obj_instituicao = new clsPmieducarInstituicao($det_escola['ref_cod_instituicao']);
      $det_instituicao = $obj_instituicao->detalhe();
      $this->nm_instituicao = $det_instituicao['nm_instituicao'];
    }

    $obj_calendario = new clsPmieducarEscolaAnoLetivo();
    $lista_calendario = $obj_calendario->lista($this->ref_cod_escola, $this->ano,
      NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL);

    $obj_turma = new clsPmieducarTurma($this->ref_cod_turma);
    $det_turma = $obj_turma->detalhe();
    $this->nm_turma = $det_turma['nm_turma'];

    $obj_serie = new clsPmieducarSerie($this->ref_cod_serie);
    $det_serie = $obj_serie->detalhe();
    $this->nm_serie = $det_serie['nm_serie'];

    $obj_pessoa = new clsPessoa_($det_turma['ref_cod_regente']);
    $det = $obj_pessoa->detalhe();
    $this->nm_professor = $det['nome'];

    if (!$lista_calendario) {
      echo '
        <script>
          alert("Escola n�o possui calend�rio definido para este ano");
          window.parent.fechaExpansivel(\'div_dinamico_\' + (window.parent.DOM_divs.length - 1));
        </script>';

      return TRUE;
    }

    $altura_linha     = 23;
    $inicio_escrita_y = 175;

    $obj = new clsPmieducarSerie();
    $obj->setOrderby('cod_serie, etapa_curso');
    $lista_serie_curso = $obj->lista(NULL, NULL, NULL,$this->ref_cod_curso, NULL,
      NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, $this->ref_cod_instituicao);

    $obj_curso = new clsPmieducarCurso($this->ref_cod_curso);
    $det_curso = $obj_curso->detalhe();

    if ($det_curso['falta_ch_globalizada']) {
      // N�meros de semanas no m�s
      $db = new clsBanco();
      $consulta = sprintf("SELECT padrao_ano_escolar FROM pmieducar.curso WHERE cod_curso = '%d'", $this->ref_cod_curso);
      $padrao_ano_escolar = $db->CampoUnico($consulta);
      $total_semanas = 0;

      if ($padrao_ano_escolar) {
        $meses = $db->CampoUnico(sprintf("
          SELECT
            to_char(data_inicio, 'dd/mm') || '-' || to_char(data_fim, 'dd/mm')
          FROM
            pmieducar.ano_letivo_modulo,
            pmieducar.modulo
          WHERE
            modulo.cod_modulo = ano_letivo_modulo.ref_cod_modulo
            AND modulo.ativo = 1
            AND ano_letivo_modulo.ref_cod_modulo = '%d'
            AND ano_letivo_modulo.sequencial = '%d'
            AND ref_ano = '%d'
            AND ref_ref_cod_escola = '%d'
          ORDER BY
            data_inicio,data_fim ASC
        ", $this->ref_cod_modulo, $this->sequencial, $this->ano, $this->ref_cod_escola));

        $data = explode('-', $meses);

        if (!$this->data_ini) {
          $this->data_ini = $data[0];
        }

        if (!$this->data_fim) {
          $this->data_fim = $data[1];
        }

        $data_ini = explode('/', $data[0]);
        $data_fim = explode('/', $data[1]);

        $meses = array($data_ini[1],$data_fim[1]);
        $dias  = array($data_ini[0],$data_fim[0]);

        $total_semanas = 0;

        for ($mes = (int) $meses[0]; $mes <= (int) $meses[1]; $mes++) {
          $mes_final = FALSE;

          if ($mes == (int) $meses[0]) {
            $dia = $dias[0];
          }
          elseif ($mes == (int)$meses[1]) {
            $dia       = $dias[1];
            $mes_final = TRUE;
          }
          else {
            $dia = 1;
          }

          $total_semanas += $this->getNumeroDiasMes($dia, $mes, $this->ano, $mes_final);
        }
      }
      else {
        $meses = $db->CampoUnico(sprintf("
          SELECT
            to_char(data_inicio, 'dd/mm') || '-' || to_char(data_fim, 'dd/mm')
          FROM
            pmieducar.turma_modulo,
            pmieducar.modulo
          WHERE
            modulo.cod_modulo = turma_modulo.ref_cod_modulo
            AND ref_cod_turma = '%d'
            AND turma_modulo.ref_cod_modulo = '%d'
            AND turma_modulo.sequencial = '%d'
            AND to_char(data_inicio,'yyyy') = '%d'
          ORDER BY
            data_inicio,data_fim ASC
        ", $this->ref_cod_turma, $this->ref_cod_modulo, $this->sequencial, $this->ano));

        $total_semanas = 0;

        $data = explode('-', $meses);

        if (!$this->data_ini) {
          $this->data_ini = $data[0];
        }

        if (!$this->data_fim) {
          $this->data_fim = $data[1];
        }

        $data_ini = explode('/', $data[0]);
        $data_fim = explode('/', $data[1]);

        $meses = array($data_ini[1],$data_fim[1]);
        $dias  = array($data_ini[0],$data_fim[0]);

        $total_semanas = 0;

        for ($mes = $meses[0]; $mes <= $meses[1]; $mes++) {
          $mes_final = FALSE;

          if ($mes == $meses[0]) {
            $dia = $dias[0];
          }
          elseif ($mes == $meses[1]) {
            $dia       = 1;
            $mes_final = TRUE;
          }
          else {
            $dia = 1;
          }

          $total_semanas += $this->getNumeroDiasMes($dia, $mes, $this->ano, $mes_final);
        }
      }

      $this->pdf = new clsPDF('Di�rio de Classe - ' . $this->ano,
        sprintf('Di�rio de Classe - %s at� %s de %s', $data[0], $data[1], $this->ano),
        'A4', '', FALSE, FALSE
      );

      $this->mes_inicial   = (int) $meses[0];
      $this->mes_final     = (int) $meses[1];
      $this->pdf->largura  = 842.0;
      $this->pdf->altura   = 595.0;

      $this->total = $total_semanas;

      if (!$this->em_branco) {
        $obj_matricula_turma = new clsPmieducarMatriculaTurma();
        $obj_matricula_turma->setOrderby('nome_ascii');
        $lista_matricula = $obj_matricula_turma->lista(NULL, $this->ref_cod_turma,
          NULL, NULL, NULL, NULL, NULL, NULL, 1, $this->ref_cod_serie,
          $this->ref_cod_curso, $this->ref_cod_escola, $this->ref_cod_instituicao,
          NULL, NULL, array(1, 2, 3), NULL, NULL, $this->ano, NULL, TRUE, NULL,
          NULL, TRUE);
      }

      if ($lista_matricula || $this->em_branco) {
        $this->pdf->OpenPage();
        $this->addCabecalho();

        if ($this->em_branco) {
          $lista_matricula = array();
          $this->numero_registros = $this->numero_registros ?
            $this->numero_registros : 20;

          for ($i = 0 ; $i < $this->numero_registros; $i++) {
            $lista_matricula[] = '';
          }
        }

        $num = 0;
        foreach ($lista_matricula as $matricula) {
          $num++;

          if ($this->page_y > 500) {
            $this->desenhaLinhasVertical();
            $this->pdf->ClosePage();
            $this->pdf->OpenPage();
            $this->page_y = 125;
            $this->addCabecalho();
          }

          $this->pdf->quadrado_relativo(30, $this->page_y , 782, 19);
          $this->pdf->escreve_relativo($matricula['nome_aluno'] , 33,
            $this->page_y + 4, 160, 15, $fonte, 7, $corTexto, 'left');

          $this->pdf->escreve_relativo(sprintf('%02d', $num), 757,
            $this->page_y + 4, 30, 30, $fonte, 7, $corTexto, 'left');

          $this->page_y +=19;
        }

        $this->desenhaLinhasVertical();

        $this->rodape();
        $this->pdf->ClosePage();
      }
      else {
        echo '
          <script>
            alert("Turma n�o possui matriculas");
            window.parent.fechaExpansivel(\'div_dinamico_\'+(window.parent.DOM_divs.length-1));
          </script>';

        return;
      }

      $this->pdf->CloseFile();
      $this->get_link = $this->pdf->GetLink();
    }
    else {
      // Carga hor�ria n�o globalizada gera relat�rio com uma disciplina por
      // p�gina
      $obj_turma_disc = new clsPmieducarDisciplinaSerie();
      $obj_turma_disc->setCamposLista('ref_cod_disciplina');
      $lst_turma_disc = $obj_turma_disc->lista(NULL, $this->ref_cod_serie, 1);

      if ($lst_turma_disc) {
        $this->pdf = new clsPDF('Di�rio de Classe - ' . $this->ano,
          sprintf('Di�rio de Classe - %s at� %s de %s', $this->data_ini, $this->data_fim, $this->ano),
          'A4', '', FALSE, FALSE
        );

        foreach ($lst_turma_disc as $disciplina) {
          $obj_disc = new clsPmieducarDisciplina($disciplina);
          $det_disc = $obj_disc->detalhe();
          $this->nm_disciplina = $det_disc['nm_disciplina'];
          $this->page_y = 125;

          // N�mero de semanas dos meses
          $obj_quadro = new clsPmieducarQuadroHorario();
          $obj_quadro->setCamposLista('cod_quadro_horario');
          $quadro_horario = $obj_quadro->lista(NULL, NULL, NULL, $this->ref_cod_turma,
            NULL, NULL, NULL, NULL, 1);

          if (!$quadro_horario) {
            echo '<script>alert(\'Turma n�o possui quadro de hor�rios\');
            window.parent.fechaExpansivel(\'div_dinamico_\'+(window.parent.DOM_divs.length-1));</script>';
            die();
          }

          $obj_quadro_horarios = new clsPmieducarQuadroHorarioHorarios();
          $obj_quadro_horarios->setCamposLista('dia_semana');
          $obj_quadro_horarios->setOrderby('1 asc');

          $lista_quadro_horarios = $obj_quadro_horarios->lista($quadro_horario[0],
            $this->ref_cod_serie, $this->ref_cod_escola, $disciplina, NULL, NULL,
            NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);

          $db       = new clsBanco();
          $consulta = sprintf('SELECT padrao_ano_escolar FROM pmieducar.curso WHERE cod_curso = \'%d\'', $this->ref_cod_curso);
          $padrao_ano_escolar = $db->CampoUnico($consulta);

          $total_semanas = 0;

          if ($padrao_ano_escolar) {
            $meses = $db->CampoUnico(sprintf("
              SELECT
                to_char(data_inicio, 'dd/mm') || '-' || to_char(data_fim, 'dd/mm')
              FROM
                pmieducar.ano_letivo_modulo,
                pmieducar.modulo
              WHERE
                modulo.cod_modulo = ano_letivo_modulo.ref_cod_modulo
                AND modulo.ativo = 1
                AND ano_letivo_modulo.ref_cod_modulo = '%d'
                AND ano_letivo_modulo.sequencial = '%d'
                AND ref_ano = '%d'
                AND ref_ref_cod_escola = '%d'
              ORDER BY
                data_inicio,data_fim ASC
            ", $this->ref_cod_modulo, $this->sequencial, $this->ano, $this->ref_cod_escola));

            $data = explode('-', $meses);

            if (!$this->data_ini) {
              $this->data_ini = $data[0];
            }

            if (!$this->data_fim) {
              $this->data_fim = $data[1];
            }

            $data_ini = explode('/', $data[0]);
            $data_fim = explode('/', $data[1]);

            $meses = array($data_ini[1], $data_fim[1]);
            $dias  = array($data_ini[0], $data_fim[0]);
          }
          else
          {
            $meses = $db->CampoUnico(sprintf("
              SELECT
                to_char(data_inicio, 'mm') || '-' || to_char(data_fim, 'mm')
              FROM
                pmieducar.turma_modulo,
                pmieducar.modulo
              WHERE
                modulo.cod_modulo = turma_modulo.ref_cod_modulo
                AND ref_cod_turma = '%d'
                AND turma_modulo.ref_cod_modulo = '%d'
                AND turma_modulo.sequencial = '%d'
                AND to_char(data_inicio,'yyyy') = '%d'
              ORDER BY
                data_inicio, data_fim ASC
            ", $this->ref_cod_turma, $this->ref_cod_modulo, $this->sequencial, $this->ano));

            $data = explode('-', $meses);

            if (!$this->data_ini) {
              $this->data_ini = $data[0];
            }

            if (!$this->data_fim) {
              $this->data_fim = $data[1];
            }

            $data_ini = explode('/', $data[0]);
            $data_fim = explode('/', $data[1]);

            $meses = array($data_ini[1], $data_fim[1]);
            $dias  = array($data_ini[0], $data_fim[0]);
          }

          $total_dias_semanas = 0;

          if($lista_quadro_horarios) {
            for($mes_ = $meses[0]; $mes_ <= $meses[1]; $mes_++) {
              $mes_final = FALSE;

              foreach ($lista_quadro_horarios as $dia_semana) {
                if($mes_ == $meses[0]) {
                  $dia = $dias[0];
                }
                elseif ($mes == $meses[1]) {
                  $dia = 1;
                  $mes_final = TRUE;
                }
                else {
                  $dia = 1;
                }

                $total_dias_semanas += $this->getDiasSemanaMes(
                  $dia, $mes_,$this->ano,$dia_semana,$mes_final
                );
              }
            }

            $this->mes_inicial  = (int) $meses[0];
            $this->mes_final    = (int) $meses[1];
            $this->pdf->largura = 842.0;
            $this->pdf->altura  = 595.0;

            $this->total = $total_dias_semanas;

            if (!$this->total) {
              break;
            }
          }

          if (!$this->em_branco) {
            $obj_matricula_turma = new clsPmieducarMatriculaTurma();
            $obj_matricula_turma->setOrderby('nome_ascii');
            $lista_matricula = $obj_matricula_turma->lista(NULL, $this->ref_cod_turma,
              NULL, NULL, NULL, NULL, NULL, NULL, 1, $this->ref_cod_serie,
              $this->ref_cod_curso, $this->ref_cod_escola, $this->ref_cod_instituicao,
              NULL, NULL, array(1, 2, 3), NULL, NULL, $this->ano, NULL, TRUE, NULL,
              NULL, TRUE
            );
          }

          if ($lista_matricula || $this->em_branco) {
            $this->pdf->OpenPage();
            $this->addCabecalho();

            if ($this->em_branco) {
              $lista_matricula = array();
              $this->numero_registros = $this->numero_registros ? $this->numero_registros : 20;

              for ($i = 0 ; $i < $this->numero_registros; $i++) {
                $lista_matricula[] = '';
              }
            }

            $num = 0;
            foreach ($lista_matricula as $matricula) {
              $num++;

              if ($this->page_y > 500) {
                $this->desenhaLinhasVertical();
                $this->pdf->ClosePage();
                $this->pdf->OpenPage();
                $this->page_y = 125;
                $this->addCabecalho();
              }

              $this->pdf->quadrado_relativo(30, $this->page_y, 782, 19);

              $this->pdf->escreve_relativo($matricula['nome_aluno'] , 33,
                $this->page_y + 4, 160, 15, $fonte, 7, $corTexto, 'left');

              $this->pdf->escreve_relativo(sprintf('%02d', $num), 757,
                $this->page_y + 4, 30, 30, $fonte, 7, $corTexto, 'left');

              $this->page_y +=19;
            }

            $this->desenhaLinhasVertical();
            $this->pdf->ClosePage();
          }
          else {
            echo '
              <script>
                alert("Turma n�o possui matr�culas");
                window.parent.fechaExpansivel(\'div_dinamico_\' + (window.parent.DOM_divs.length - 1));
              </script>';

            return;
          }
        }
      }

      if ($this->total) {
        $this->pdf->CloseFile();
        $this->get_link = $this->pdf->GetLink();
      }
      else {
        $this->mensagem = 'N�o existem dias letivos cadastrados para esta turma';
      }
    }

    echo sprintf('
      <script>
        window.onload = function()
        {
          parent.EscondeDiv("LoadImprimir");
          window.location="download.php?filename=%s"
        }
      </script>', $this->get_link);

    echo sprintf('
      <html>
        <center>
          Se o download n�o iniciar automaticamente <br /><a target="blank" href="%s" style="font-size: 16px; color: #000000; text-decoration: underline;">clique aqui!</a><br><br>
          <span style="font-size: 10px;">Para visualizar os arquivos PDF, � necess�rio instalar o Adobe Acrobat Reader.<br>
            Clique na Imagem para Baixar o instalador<br><br>
            <a href="http://www.adobe.com.br/products/acrobat/readstep2.html" target="new"><br><img src="imagens/acrobat.gif" width="88" height="31" border="0"></a>
          </span>
        </center>
      </html>', $this->get_link);
  }


  public function addCabecalho()
  {
    /**
     * Vari�vel global com objetos do CoreExt.
     * @see includes/bootstrap.php
     */
    global $coreExt;

    // Namespace de configura��o do template PDF
    $config = $coreExt['Config']->app->template->pdf;

    // Vari�vel que controla a altura atual das caixas
    $altura   = 30;
    $fonte    = 'arial';
    $corTexto = '#000000';

    // Cabe�alho
    $logo = $config->get($config->logo, 'imagens/brasao.gif');

    $this->pdf->quadrado_relativo( 30, $altura, 782, 85 );
    $this->pdf->insertImageScaled('gif', $logo, 50, 95, 41);

    // T�tulo principal
    $titulo = $config->get($config->titulo, 'i-Educar');

    $this->pdf->escreve_relativo($titulo, 30, 30, 782, 80, $fonte, 18, $corTexto, 'center');

    $this->pdf->escreve_relativo(date('d/m/Y'), 25, 30, 782, 80, $fonte, 10, $corTexto, 'right' );

    // Dados escola
    $this->pdf->escreve_relativo('Institui��o: ' . $this->nm_instituicao, 120, 52,
      300, 80, $fonte, 7, $corTexto, 'left');

    $this->pdf->escreve_relativo('Escola: ' . $this->nm_escola,132, 64, 300, 80,
      $fonte, 7, $corTexto, 'left');

    $dif = 0;

    if ($this->nm_professor) {
      $this->pdf->escreve_relativo('Prof. Regente: ' . $this->nm_professor,111, 76,
        300, 80, $fonte, 7, $corTexto, 'left');
    }
    else {
      $dif = 12;
    }

    $this->pdf->escreve_relativo('S�rie: ' . $this->nm_serie,138, 88  - $dif,
      300, 80, $fonte, 7, $corTexto, 'left');

    $this->pdf->escreve_relativo('Turma: ' . $this->nm_turma,134, 100 - $dif, 300,
      80, $fonte, 7, $corTexto, 'left');

    // T�tulo
    $nm_disciplina = '';
    if ($this->nm_disciplina) {
      $nm_disciplina = ' - ' . $this->nm_disciplina;
    }

    $this->pdf->escreve_relativo('Di�rio de Frequ�ncia ' . $nm_disciplina, 30,
      75, 782, 80, $fonte, 12, $corTexto, 'center');

    $obj_modulo = new clsPmieducarModulo($this->ref_cod_modulo);
    $det_modulo = $obj_modulo->detalhe();

    // Data
    $this->pdf->escreve_relativo(
      sprintf('%s at� %s de %s', $this->data_ini, $this->data_fim, $this->ano),
      45, 100, 782, 80, $fonte, 10, $corTexto, 'center');

    $this->pdf->linha_relativa(201, 125, 612, 0);

    $this->page_y += 19;

    $this->pdf->escreve_relativo('Dias de aula: ' . $this->total, 715, 100, 535,
      80, $fonte, 10, $corTexto, 'left');
  }


  function desenhaLinhasVertical()
  {
    $corTexto = '#000000';

    $largura_anos = 550;

    if ($this->total >= 1) {
      $incremental = floor($largura_anos / ($this->total +1)) ;
    }
    else {
      $incremental = 1;
    }

    $reta_ano_x = 200 ;

    $resto = $largura_anos - ($incremental * $this->total);

    for ($linha = 0; $linha < $this->total + 1; $linha++) {
      if (($resto > 0) || $linha == 0) {
        $reta_ano_x++;
        $resto--;
      }

      $this->pdf->linha_relativa($reta_ano_x, 125, 0, $this->page_y - 125);

      $reta_ano_x += $incremental;
    }

    $this->pdf->linha_relativa(812, 125, 0, $this->page_y - 125);

    $this->pdf->escreve_relativo('N�:', 755, 128, 100, 80, $fonte, 7, $corTexto, 'left');

    $this->pdf->linha_relativa(775, 125, 0, $this->page_y - 125);

    $this->pdf->escreve_relativo('Faltas', 783, 128, 100, 80, $fonte, 7, $corTexto, 'left');

    $this->rodape();
    $this->pdf->ClosePage();
    $this->pdf->OpenPage();
    $this->page_y = 125;
    $this->addCabecalho();

    for ($ct = 125; $ct < 500; $ct += 19) {
      $this->pdf->quadrado_relativo(30, $ct , 782, 19);
    }

    $this->pdf->escreve_relativo('Observa��es', 30, 130, 782, 30, $fonte, 7,
      $corTexto, 'center');

    $this->pdf->linha_relativa(418, 144, 0, 360);
  }

  function rodape()
  {
    $corTexto  = '#000000';
    $fonte     = 'arial';
    $dataAtual = date('d/m/Y');
    $this->pdf->escreve_relativo('Data: ' . $dataAtual, 36,795, 100, 50, $fonte,
      7, $corTexto, 'left');

    $this->pdf->escreve_relativo('Assinatura do Professor(a)', 695, 520, 100, 50,
      $fonte, 7, $corTexto, 'left');

    $this->pdf->linha_relativa(660, 517, 130, 0);
  }

  function Editar()
  {
    return FALSE;
  }

  function Excluir()
  {
    return FALSE;
  }

  function getNumeroDiasMes($dia, $mes, $ano, $mes_final = FALSE)
  {
    $year  = $ano;
    $month = $mes;

    $date = mktime(1, 1, 1, $month, $dia, $year);

    $first_day_of_month = strtotime('-' . (date('d', $date) - 1) . ' days', $date);
    $last_day_of_month  = strtotime('+' . (date('t', $first_day_of_month) - 1) . ' days', $first_day_of_month);

    $last_day_of_month = date('d', $last_day_of_month);

    $numero_dias = 0;

    $obj_calendario = new clsPmieducarCalendarioAnoLetivo();
    $obj_calendario->setCamposLista('cod_calendario_ano_letivo');
    $lista = $obj_calendario->lista(NULL, $this->ref_cod_escola, NULL, NULL,
      $this->ano, NULL, NULL, NULL, NULL, 1);

    if ($lista) {
      $lista_calendario = array_shift($lista);
    }

    $obj_dia = new clsPmieducarCalendarioDia();
    $obj_dia->setCamposLista('dia');
    $dias_nao_letivo = $obj_dia->lista($lista_calendario, $mes, NULL, NULL, NULL,
      NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, "'n'");

    if (!$dias_nao_letivo) {
      $dias_nao_letivo = array();
    }

    if ($mes_final) {
      $last_day_of_month = $dia;
      $dia = 1;
    }

    for ($day = $dia; $day <= $last_day_of_month; $day++) {
      $date = mktime(1, 1, 1, $month, $day, $year);
      $dia_semana_corrente = getdate($date);
      $dia_semana_corrente = $dia_semana_corrente['wday'] + 1;

      if (($dia_semana_corrente != 1 &&  $dia_semana_corrente != 7) && (array_search($day,$dias_nao_letivo) === FALSE)) {
        $numero_dias++;
      }
    }

    return $numero_dias;
  }

  function getDiasSemanaMes($dia, $mes, $ano, $dia_semana, $mes_final = FALSE)
  {
    $year  = $ano;
    $month = $mes;

    $date = mktime(1, 1, 1, $month, $dia, $year);

    $first_day_of_month = strtotime("-" . (date("d", $date) - 1) . " days", $date);
    $last_day_of_month  = strtotime("+" . (date("t", $first_day_of_month) - 1) . " days", $first_day_of_month);

    $last_day_of_month = date("d", $last_day_of_month);

    $numero_dias = 0;

    $obj_calendario = new clsPmieducarCalendarioAnoLetivo();
    $obj_calendario->setCamposLista("cod_calendario_ano_letivo");
    $lista_calendario = $obj_calendario->lista(NULL, $this->ref_cod_escola, NULL,
      NULL, $this->ano, NULL, NULL, NULL, NULL, 1);

    if (is_array($lista_calendario)) {
      $lista_calendario = array_shift($lista_calendario);
    }

    $obj_dia = new clsPmieducarCalendarioDia();
    $obj_dia->setCamposLista('dia');
    $dias_nao_letivo = $obj_dia->lista($lista_calendario,$mes, NULL, NULL, NULL,
      NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, "'n'");

    if (!$dias_nao_letivo) {
      $dias_nao_letivo = array();
    }

    if($mes_final) {
      $last_day_of_month = $dia;
      $dia = 1;
    }

    for($day = $dia; $day <= $last_day_of_month; $day++) {
      $date = mktime(1, 1, 1, $month, $day, $year);
      $dia_semana_corrente = getdate($date);
      $dia_semana_corrente = $dia_semana_corrente['wday'] + 1;

      $data_atual = sprintf("%s/%s/%s", $day, $mes, $ano);
      $data_final = sprintf("%s/%s", $this->data_fim, $ano);

      if (($dia_semana ==  $dia_semana_corrente) &&
         (array_search($day,$dias_nao_letivo) === FALSE) && data_maior($data_final, $data_atual)
      ) {
        $numero_dias++;
      }
    }

    return $numero_dias;
  }
}

// Instancia objeto de p�gina
$pagina = new clsIndexBase();

// Instancia objeto de conte�do
$miolo = new indice();

// Atribui o conte�do �  p�gina
$pagina->addForm($miolo);

// Gera o c�digo HTML
$pagina->MakeAll();