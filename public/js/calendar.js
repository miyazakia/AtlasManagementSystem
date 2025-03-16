$(function () {
  $('.js-modal-open').on('click', function () {
    $('.js-modal').fadeIn();

    // ボタンから予約日と部数、場所の情報を取得
    var reserve_date = $(this).attr('reserve_date');
    var reserve_part = $(this).attr('reserve_part');

    // モーダルにデータを挿入
    $('.modal_date').text(reserve_date); // 予約日
    $('.modal_part').text(reserve_part); // 部数と場所

    // hiddenフィールドに値をセット
    $('#getDate').val(reserve_date);
    $('#getPart').val(reserve_part);
    console.log(setting_reserve, reserve_part);

    return false; // モーダル表示後、リンクの遷移を防ぐ
  });

  $('.js-modal-close').on('click', function () {
    $('.js-modal').fadeOut(); // モーダルを非表示にする
    return false; // 閉じるボタンのデフォルト動作を防ぐ
  });
});
