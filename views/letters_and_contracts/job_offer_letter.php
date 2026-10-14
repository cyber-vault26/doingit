<div class=" container  mx-auto">
                  <h4 class=" text-center text-primary">Job Offer Letter form</h4>

                  <form action="./pdfs/generate_joboffer_letter.php" method="post">
                      <div class=" col-sm-10 col-md-6 col-lg-6">
                          <div class=" mb-3 mt-3">

                             <label for="" class=" form-label">Staff name</label>
                             <input type="text" name="name" required class=" form-control"  id="">
                          </div>
                          <div class=" mb-2 mt-2">
                              <label for="" class=" form-label ">Date</label>
                              <input type="date" name="date" class=" form-control" required id="">
                          </div>
                          <div class=" mt-2 mb-2">
                              <label for="" class=" form-label ">Job title</label>
                              <input type="text" name="job_title" required class=" form-control" id="">
                          </div>
                          <div class=" mt-2 mb-2">
                              <label for="" class=" form-label">Expected basic salary</label>
                              <input type="number" name="salary" id="" class=" form-control" required>
                          </div>

                      </div> 
                      <button type="submit" class=" btn btn-success">Generate letter</button>
                  </form>

              </div>