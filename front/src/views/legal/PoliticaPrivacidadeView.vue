<script setup lang="ts">
import { useRouter } from "vue-router"
import { Button } from "@/components/ui/button"
import { ArrowLeft } from "lucide-vue-next"

const router = useRouter()
</script>

<template>
  <div class="min-h-screen bg-background">
    <div class="container max-w-4xl mx-auto py-8 px-4">
      <Button variant="ghost" size="sm" class="mb-6" @click="router.back()">
        <ArrowLeft class="size-4 mr-2" />
        Voltar
      </Button>

      <article class="prose prose-neutral dark:prose-invert max-w-none">
        <h1>Politica de Privacidade</h1>
        <p class="text-muted-foreground">Ultima atualizacao: 21 de fevereiro de 2026</p>

        <p>
          Esta Politica de Privacidade descreve como a <strong>E DE J L BARBOSA CONSULTORIA LTDA - ME</strong>
          ("Empresa", "nos"), inscrita no CNPJ sob o n. 42.623.370/0001-25, coleta, utiliza, armazena
          e protege os dados pessoais dos usuarios da plataforma Grapo ("Servico"), em conformidade
          com a Lei Geral de Protecao de Dados Pessoais (Lei n. 13.709/2018 - LGPD).
        </p>

        <h2>1. Dados Pessoais Coletados</h2>
        <p>Coletamos os seguintes dados pessoais:</p>

        <h3>1.1. Dados fornecidos diretamente por voce:</h3>
        <ul>
          <li><strong>Dados de cadastro:</strong> nome completo, e-mail, senha (criptografada), telefone;</li>
          <li><strong>Dados do locador:</strong> nome/razao social, CPF/CNPJ, endereco, dados bancarios;</li>
          <li><strong>Dados de locatarios:</strong> nome, CPF/CNPJ, endereco, telefone, e-mail;</li>
          <li><strong>Dados de pagamento:</strong> processados diretamente pelo Stripe (nao armazenamos dados de cartao).</li>
        </ul>

        <h3>1.2. Dados coletados automaticamente:</h3>
        <ul>
          <li><strong>Dados de acesso:</strong> endereco IP, tipo de navegador, sistema operacional;</li>
          <li><strong>Dados de uso:</strong> paginas acessadas, funcionalidades utilizadas, data e hora de acesso;</li>
          <li><strong>Cookies:</strong> utilizados para autenticacao e preferencias do usuario.</li>
        </ul>

        <h2>2. Finalidades do Tratamento</h2>
        <p>Utilizamos seus dados pessoais para:</p>
        <ul>
          <li><strong>Execucao do contrato:</strong> fornecer o Servico contratado, incluindo gestao de ativos, contratos e pagamentos;</li>
          <li><strong>Comunicacao:</strong> enviar notificacoes sobre sua conta, contratos e pagamentos;</li>
          <li><strong>Melhoria do Servico:</strong> analisar o uso para aprimorar funcionalidades;</li>
          <li><strong>Seguranca:</strong> prevenir fraudes e proteger a integridade do Servico;</li>
          <li><strong>Obrigacoes legais:</strong> cumprir obrigacoes fiscais, contabeis e regulatorias;</li>
          <li><strong>Marketing:</strong> enviar comunicacoes promocionais, mediante seu consentimento.</li>
        </ul>

        <h2>3. Bases Legais para o Tratamento (Art. 7, LGPD)</h2>
        <p>O tratamento de seus dados pessoais e fundamentado nas seguintes bases legais:</p>
        <ul>
          <li><strong>Execucao de contrato (Art. 7, V):</strong> para fornecer o Servico contratado;</li>
          <li><strong>Consentimento (Art. 7, I):</strong> para comunicacoes de marketing e cookies nao essenciais;</li>
          <li><strong>Cumprimento de obrigacao legal (Art. 7, II):</strong> para obrigacoes fiscais e regulatorias;</li>
          <li><strong>Legitimo interesse (Art. 7, IX):</strong> para melhoria do Servico e seguranca, respeitando seus direitos.</li>
        </ul>

        <h2>4. Compartilhamento de Dados</h2>
        <p>Seus dados pessoais podem ser compartilhados com:</p>
        <ul>
          <li>
            <strong>Stripe:</strong> processador de pagamentos, para viabilizar transacoes financeiras.
            O Stripe possui sua propria politica de privacidade e e certificado PCI-DSS;
          </li>
          <li>
            <strong>Provedores de infraestrutura:</strong> servicos de hospedagem e armazenamento em nuvem;
          </li>
          <li>
            <strong>Autoridades competentes:</strong> quando exigido por lei ou ordem judicial.
          </li>
        </ul>
        <p>
          Nao vendemos, alugamos ou comercializamos seus dados pessoais com terceiros para fins de marketing.
        </p>

        <h2>5. Transferencia Internacional de Dados</h2>
        <p>
          Alguns de nossos provedores de servicos podem estar localizados fora do Brasil. Nestes casos,
          garantimos que a transferencia internacional de dados seja realizada em conformidade com a
          LGPD, mediante clausulas contratuais padrao ou outras salvaguardas adequadas.
        </p>

        <h2>6. Retencao de Dados</h2>
        <p>
          Seus dados pessoais serao retidos pelo tempo necessario para cumprir as finalidades descritas
          nesta Politica, observando os seguintes criterios:
        </p>
        <ul>
          <li><strong>Dados de conta:</strong> enquanto sua conta estiver ativa e por 5 anos apos o encerramento;</li>
          <li><strong>Dados de contratos e pagamentos:</strong> pelo prazo legal de guarda de documentos fiscais (5 anos);</li>
          <li><strong>Dados de acesso:</strong> por 6 meses para fins de seguranca;</li>
          <li><strong>Dados de marketing:</strong> ate a revogacao do consentimento.</li>
        </ul>

        <h2>7. Seus Direitos como Titular (Art. 18, LGPD)</h2>
        <p>Voce possui os seguintes direitos em relacao aos seus dados pessoais:</p>
        <ul>
          <li><strong>Confirmacao e acesso:</strong> confirmar a existencia de tratamento e acessar seus dados;</li>
          <li><strong>Correcao:</strong> solicitar a correcao de dados incompletos, inexatos ou desatualizados;</li>
          <li><strong>Anonimizacao, bloqueio ou eliminacao:</strong> de dados desnecessarios ou tratados em desconformidade;</li>
          <li><strong>Portabilidade:</strong> obter seus dados em formato estruturado para transferencia a outro fornecedor;</li>
          <li><strong>Eliminacao:</strong> solicitar a exclusao de dados tratados com base no consentimento;</li>
          <li><strong>Informacao sobre compartilhamento:</strong> saber com quais entidades seus dados foram compartilhados;</li>
          <li><strong>Revogacao do consentimento:</strong> retirar seu consentimento a qualquer momento;</li>
          <li><strong>Oposicao:</strong> opor-se ao tratamento realizado com base em outras hipoteses legais, em caso de descumprimento da LGPD.</li>
        </ul>
        <p>
          Para exercer seus direitos, entre em contato pelo e-mail: <strong>evaldo@code2.com.br</strong>.
          Responderemos sua solicitacao em ate 15 dias uteis.
        </p>

        <h2>8. Seguranca dos Dados</h2>
        <p>
          Adotamos medidas tecnicas e organizacionais para proteger seus dados pessoais, incluindo:
        </p>
        <ul>
          <li>Criptografia de dados em transito (HTTPS/TLS) e em repouso;</li>
          <li>Senhas armazenadas com hash seguro (bcrypt);</li>
          <li>Controle de acesso baseado em funcoes;</li>
          <li>Monitoramento e logs de acesso;</li>
          <li>Backups regulares e plano de recuperacao.</li>
        </ul>

        <h2>9. Cookies</h2>
        <p>
          Utilizamos cookies essenciais para o funcionamento do Servico, como autenticacao e
          preferencias. Cookies de analise podem ser utilizados para melhorar o Servico,
          mediante seu consentimento.
        </p>
        <p>
          Voce pode configurar seu navegador para recusar cookies, mas isso pode afetar
          algumas funcionalidades do Servico.
        </p>

        <h2>10. Menores de Idade</h2>
        <p>
          O Servico nao e destinado a menores de 18 anos. Nao coletamos intencionalmente dados
          de menores. Caso tome conhecimento de que um menor forneceu dados pessoais, entre em
          contato para que possamos elimina-los.
        </p>

        <h2>11. Alteracoes nesta Politica</h2>
        <p>
          Esta Politica pode ser atualizada periodicamente. Alteracoes significativas serao
          comunicadas por e-mail ou aviso no Servico. Recomendamos revisar esta pagina
          regularmente.
        </p>

        <h2>12. Encarregado de Dados (DPO)</h2>
        <p>
          Para questoes relacionadas a protecao de dados pessoais, entre em contato com nosso
          Encarregado de Dados:
        </p>
        <ul>
          <li><strong>Nome:</strong> Evaldo Barbosa</li>
          <li><strong>E-mail:</strong> evaldo@code2.com.br</li>
        </ul>

        <h2>13. Contato</h2>
        <p>
          Para duvidas sobre esta Politica de Privacidade ou sobre o tratamento de seus dados:
        </p>
        <ul>
          <li><strong>E-mail:</strong> evaldo@code2.com.br</li>
          <li><strong>Empresa:</strong> E DE J L BARBOSA CONSULTORIA LTDA - ME</li>
          <li><strong>CNPJ:</strong> 42.623.370/0001-25</li>
          <li><strong>Endereco:</strong> R Bom Jesus, 158, Cond Maria Fernanda, Turu, Sao Luis/MA, CEP 65066-210</li>
        </ul>

        <h2>14. Autoridade Nacional de Protecao de Dados</h2>
        <p>
          Caso entenda que o tratamento de seus dados pessoais viola a LGPD, voce tem o direito
          de apresentar reclamacao perante a Autoridade Nacional de Protecao de Dados (ANPD).
        </p>
        <p>
          <strong>Site:</strong> <a href="https://www.gov.br/anpd" target="_blank" rel="noopener">www.gov.br/anpd</a>
        </p>
      </article>
    </div>
  </div>
</template>
