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
        <h1>Termos de Uso</h1>
        <p class="text-muted-foreground">Ultima atualizacao: 21 de fevereiro de 2026</p>

        <h2>1. Aceitacao dos Termos</h2>
        <p>
          Ao acessar e utilizar a plataforma Grapo ("Servico"), disponivel em grapo.app, voce concorda
          em cumprir e estar vinculado aos presentes Termos de Uso. Caso nao concorde com qualquer
          disposicao destes termos, nao utilize o Servico.
        </p>
        <p>
          O Servico e fornecido por <strong>E DE J L BARBOSA CONSULTORIA LTDA - ME</strong>,
          inscrita no CNPJ sob o n. 42.623.370/0001-25, com sede em Sao Luis/MA ("Empresa", "nos").
        </p>

        <h2>2. Descricao do Servico</h2>
        <p>
          O Grapo e uma plataforma de gestao de aluguel de ativos destinada a pessoas fisicas e
          pequenas empresas. O Servico permite:
        </p>
        <ul>
          <li>Cadastro e gestao de ativos disponiveis para locacao;</li>
          <li>Controle de lotes e estoque de ativos;</li>
          <li>Criacao e gestao de contratos de locacao;</li>
          <li>Processamento de pagamentos via gateway de terceiros (Stripe);</li>
          <li>Emissao de relatorios financeiros e de rentabilidade;</li>
          <li>Gestao de locatarios e relacionamentos comerciais.</li>
        </ul>

        <h2>3. Cadastro e Conta de Usuario</h2>
        <p>
          Para utilizar o Servico, voce devera criar uma conta fornecendo informacoes verdadeiras,
          completas e atualizadas. Voce e responsavel por:
        </p>
        <ul>
          <li>Manter a confidencialidade de suas credenciais de acesso;</li>
          <li>Todas as atividades realizadas em sua conta;</li>
          <li>Notificar imediatamente a Empresa sobre qualquer uso nao autorizado.</li>
        </ul>
        <p>
          A Empresa reserva-se o direito de suspender ou encerrar contas que violem estes Termos
          ou que apresentem atividades suspeitas.
        </p>

        <h2>4. Planos e Pagamentos</h2>
        <p>
          O Servico oferece diferentes planos de assinatura. Ao contratar um plano pago, voce concorda que:
        </p>
        <ul>
          <li>Os valores serao cobrados conforme o plano selecionado;</li>
          <li>Os pagamentos sao processados por terceiros (Stripe), sujeitos aos termos destes;</li>
          <li>Nao ha reembolso proporcional em caso de cancelamento antecipado, salvo disposicao legal;</li>
          <li>A Empresa pode alterar precos mediante aviso previo de 30 dias.</li>
        </ul>

        <h2>5. Periodo de Teste Gratuito</h2>
        <p>
          Novos usuarios podem usufruir de um periodo de teste gratuito de 7 (sete) dias.
          Apos este periodo, o acesso sera limitado ate a contratacao de um plano pago.
        </p>

        <h2>6. Uso Aceitavel</h2>
        <p>Voce concorda em nao utilizar o Servico para:</p>
        <ul>
          <li>Atividades ilegais ou que violem direitos de terceiros;</li>
          <li>Transmitir conteudo malicioso, virus ou codigo prejudicial;</li>
          <li>Tentar acessar sistemas ou dados de outros usuarios;</li>
          <li>Sobrecarregar intencionalmente a infraestrutura do Servico;</li>
          <li>Revender ou sublicenciar o acesso ao Servico sem autorizacao.</li>
        </ul>

        <h2>7. Propriedade Intelectual</h2>
        <p>
          Todo o conteudo do Servico, incluindo software, design, logotipos e textos, e de
          propriedade da Empresa ou de seus licenciadores. Voce recebe uma licenca limitada,
          nao exclusiva e revogavel para uso pessoal ou comercial interno, conforme permitido
          pelo plano contratado.
        </p>
        <p>
          Os dados inseridos por voce no Servico permanecem de sua propriedade. A Empresa
          possui licenca para processar esses dados conforme necessario para fornecer o Servico.
        </p>

        <h2>8. Limitacao de Responsabilidade</h2>
        <p>
          O Servico e fornecido "como esta" e "conforme disponibilidade". A Empresa nao garante
          que o Servico sera ininterrupto ou livre de erros.
        </p>
        <p>
          Na extensao maxima permitida por lei, a Empresa nao sera responsavel por:
        </p>
        <ul>
          <li>Danos indiretos, incidentais ou consequenciais;</li>
          <li>Perda de lucros, dados ou oportunidades de negocio;</li>
          <li>Acoes ou omissoes de terceiros, incluindo processadores de pagamento;</li>
          <li>Interrupcoes causadas por fatores fora de seu controle.</li>
        </ul>
        <p>
          A responsabilidade total da Empresa, quando aplicavel, limita-se ao valor pago pelo
          usuario nos ultimos 12 meses.
        </p>

        <h2>9. Indenizacao</h2>
        <p>
          Voce concorda em indenizar e isentar a Empresa de quaisquer reclamacoes, danos ou
          despesas decorrentes do seu uso do Servico ou violacao destes Termos.
        </p>

        <h2>10. Modificacoes dos Termos</h2>
        <p>
          A Empresa pode modificar estes Termos a qualquer momento. Alteracoes significativas
          serao comunicadas por e-mail ou aviso no Servico com antecedencia minima de 15 dias.
          O uso continuado apos as alteracoes constitui aceitacao dos novos Termos.
        </p>

        <h2>11. Rescisao</h2>
        <p>
          Voce pode encerrar sua conta a qualquer momento. A Empresa pode suspender ou encerrar
          seu acesso por violacao destes Termos ou por outras razoes justificadas, mediante
          notificacao previa quando possivel.
        </p>

        <h2>12. Disposicoes Gerais</h2>
        <ul>
          <li>
            <strong>Lei Aplicavel:</strong> Estes Termos sao regidos pelas leis da Republica
            Federativa do Brasil.
          </li>
          <li>
            <strong>Foro:</strong> Fica eleito o foro da Comarca de Sao Luis/MA para dirimir
            quaisquer controversias.
          </li>
          <li>
            <strong>Integralidade:</strong> Estes Termos constituem o acordo integral entre
            as partes sobre o assunto aqui tratado.
          </li>
          <li>
            <strong>Cessao:</strong> Voce nao pode ceder seus direitos ou obrigacoes sem
            consentimento previo por escrito da Empresa.
          </li>
        </ul>

        <h2>13. Contato</h2>
        <p>
          Para duvidas sobre estes Termos de Uso, entre em contato:
        </p>
        <ul>
          <li><strong>E-mail:</strong> evaldo@code2.com.br</li>
          <li><strong>Empresa:</strong> E DE J L BARBOSA CONSULTORIA LTDA - ME</li>
          <li><strong>CNPJ:</strong> 42.623.370/0001-25</li>
        </ul>
      </article>
    </div>
  </div>
</template>
