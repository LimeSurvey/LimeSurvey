<div class="accordion">
  <div class="accordion-item">
    <h2 class="accordion-header" id="panelsStayOpen-headingOne">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
        <span class="summary-title py-1"><?php eT("Response summary"); ?></span>
      </button>
    </h2>
    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
      <div class="accordion-body">
        <div class="row">
          <div class="col-12 content-right">
            <div class="row">
              <div class="col summary-detail">
                <?php eT("Full responses"); ?>
              </div>
              <div class="col">
                <?php echo $num_completed_answers; ?>
              </div>
              <div class="col">
              </div>
            </div>
            <div class="row">
              <div class="col summary-detail">
                <?php eT("Incomplete responses"); ?>
              </div>
              <div class="col">
                <?php echo ($num_total_answers - $num_completed_answers); ?>
              </div>
              <div class="col">
              </div>
            </div>
            <div class="row">
              <div class="col summary-detail">
                <?php eT("Total responses"); ?>
              </div>
              <div class="col">
                <?php echo $num_total_answers; ?>
              </div>
              <div class="col">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>