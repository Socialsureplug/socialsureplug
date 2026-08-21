<?php

namespace Database\Seeders;

use App\Models\ServiceIcon;
use Illuminate\Database\Seeder;

class ServiceIconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Data from SMS SQL - service_icon (short_code, img_url).
     */
    public function run(): void
    {
        if (ServiceIcon::exists()) {
            return;
        }

        $icons = [
            ['wnz', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT8Br7w9JzkyIag4WOJWizaWpdaY00ib87iwXXpefg9Ug&s'],
            ['tg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/82/Telegram_logo.svg/2048px-Telegram_logo.svg.png'],
            ['mb', 'https://cdn1.iconfinder.com/data/icons/smallicons-logotypes/32/yahoo-512.png'],
            ['aa', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQVl5WB68-qQy0ZZ15B8aSvF5vxTLknwfeRP8ANlv4vEA&s'],
            ['rsh', 'https://i.pinimg.com/originals/fa/50/b2/fa50b28ca286404796f76aec8d733c6e.png'],
            ['adh', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQYjFPNq7k0aoDZz7rITMVMIcsbOeEezN0T286x4AtY1A&s'],
            ['vk', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRf6QqI3z35JSsDD4CVqYSYzVxYK6R41u3nk4AJ5Iu4IQ&s'],
            ['idg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Instagram_logo_2016.svg/2048px-Instagram_logo_2016.svg.png'],
            ['go', 'https://cdn-teams-slug.flaticon.com/google.jpg'],
            ['vs', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT8Br7w9JzkyIag4WOJWizaWpdaY00ib87iwXXpefg9Ug&s'],
            ['mjr', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcStKZknYITvQ8B_i-4dCW3F2HxHHpK7TBUqJSnk0Imw7qr21jVcfy_3z_A&s=10'],
            ['go_3', 'https://cdn-teams-slug.flaticon.com/google.jpg'],
            ['wa', 'https://cdn-icons-png.flaticon.com/512/3670/3670051.png'],
            ['dr', 'https://static-00.iconduck.com/assets.00/openai-icon-2021x2048-4rpe5x7n.png'],
            ['zx', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRtSvw9JHdU9WrikrFNJ-sCGl_vUJnHurs1pw&usqp=CAU'],
            ['ci', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTe7Xd5KmoXvaQPmaGejf5Cm7yfSp2USKw3461SQzVKUcj6ZOiQ6vOC8Co&s=10'],
            ['ck', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcStPuLN8uUGMtYg_CY9PEpXGCUDJwg1VVHDC39Jb1MzxA&s'],
            ['di', 'https://m.media-amazon.com/images/I/61VuVNzAt1L.png'],
            ['du', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQjQOQE2Abl67enqKNWQtt---S8bdF8Z7Co5O64g-D-bf_GKsB6b5WxAUw&s=10'],
            ['bb', 'https://cdn-images-1.medium.com/v2/resize:fit:1200/1*CxL6FKrFEX5uiNJXxlTfcw.png'],
            ['nav', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQalmZ0xi4zTN4sywfCE5CKccOGOmNY444uDL1P2AQltoQOJK1pYJHfunI&s=10'],
            ['tl', 'https://upload.wikimedia.org/wikipedia/commons/0/02/TrueCaller_Icon.png'],
            ['dy', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT8nRNfM_vH-KXfVsrYTz7rctEhrRscF1eobm5gzo4zYX8FbFJIJ63JMU4X&s=10'],
            ['rsj', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQyAVT-0uKLYaV1A1bgCekn_3CS0I-R2qUvLFPnntb9XjW1OvwHsDiz9btr&s=10'],
            ['smp', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3Fe48dj2Q2rGCM4reXYibjXEhLn4SPJ97FMFyxHnlMg&s'],
            ['rmb', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQP3OKGGUwoOEgTp5CWy-AgdKeISQ5n9IVq5Wg13UYdTlwQEk0Ywy6Cg1W-&s=10'],
            ['rmn', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQP3OKGGUwoOEgTp5CWy-AgdKeISQ5n9IVq5Wg13UYdTlwQEk0Ywy6Cg1W-&s=10'],
            ['ter', 'https://i0.wp.com/teenpattireferearn.com/img/teen-patti-refer-earn-logo.webp?strip=all'],
            ['rms', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSYcwXEqemY8-2lBiZcZOvE2vi5UK0cCEeTAatsTrVcGA&s'],
            ['rme', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ1rTBqukGjMng5opguwOICZoZwQlG0P-UmQBwNbjgxQA&s'],
            ['bfs', 'https://companieslogo.com/img/orig/BAJAJFINSV.NS-69a58fe4.png?t=1596838048'],
            ['cap', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ3lWTTYhuf6mn0nKyC0oS5rO0wIJ072i29d9NkGxhKGDSxXKfCdoMgwwY&s=10'],
            ['fg', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSPG2ArXzy8sC4IC1_qLnNEghPt_Io4nOkIBPSfEo6rRg3AnxRkTLQZ1U7Z&s=10'],
            ['ec', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTcOIMW2ZXQQi56CslWkTppveWFu99H-VlYmEXg7xCfmjuEb6huxyMeJ5RG&s=10'],
            ['ks', 'https://play-lh.googleusercontent.com/mkuNjtUPpNAvk0tI6NcllPqRO2jnGS3W5TJIHJGlCrOUVcaM5ZzVyFBkmUjL4H3Tdg'],
            ['id', 'https://lh3.googleusercontent.com/7ATZTNY7SHZB7hcP9qdcuJ0zA29g6hN5asl4X-UQXYDev3DFH36quT5ewUyGl7QUKLHJltOgqYsUDiHA830OFodIshxbRuvRjMw'],
            ['ex', 'https://static-00.iconduck.com/assets.00/linode-icon-427x512-4l4fs2tu.png'],
            ['ah', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcROSXnkIUmYEGMJIIDrfg80KI_uxsbLA94wVw&usqp=CAU'],
            ['fo', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR_CjocT_DXaqUNDscpPiPZAkY7G0P-v8Y_ZJzbbhCwkQ&s'],
            ['tg_3', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/82/Telegram_logo.svg/2048px-Telegram_logo.svg.png'],
            ['jx', 'https://cdn.iconscout.com/icon/free/png-256/free-swiggy-1613371-1369418.png'],
            ['adi', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTJ7BGFvKkNKTBzt2lruYxcKn2jBMVgGRvBJ_yquusnWg&s'],
            ['mm', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQMIUHKeZUSADdjxX7WJVNw3hJk_PbCEcVXtXGcLsdQrQ&s'],
            ['mi', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRC97bPKSDRspPU-WdpfssGsoVialyvbyrIXBEo7G8axg&s'],
            ['flp', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRgyhRhEGap4lug-9rI0fHdKSSMpWntXtnyZ7Cpxwg0DKpKUGSTTGsDDmo&s=10'],
            ['ge', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTkZ3AO2eCD3xqEswfI46evQ-RK1VeaPtWLgoZDpxrUuLMJPVcSx8K47DE&s=10'],
            ['rlr', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRfyW2345lfHY9bLrpYWBos09Ncr5S9Yn5uIOFf7xXZFw&s'],
            ['zl', 'https://play-lh.googleusercontent.com/uFg3zOsnGZkIrswmvXyFYhoF3gC4tv0ovFZv0zisJFQ2DZqJyh9SUGrK6D-Tnn1lGqc'],
            ['ve', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSxlCt1q-O7ztbzGEKrK8RSB7zOz6EoP7LzJ8noMt-icWVXE3QhfIf9xw8w&s=10'],
            ['rtr', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSEtfKODFf6I-vsHrYPJBnu0T_-8GlF8IpdJxy4yncS3Q&s'],
            ['nl', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ8RNQjrnnHaVq7_cIdXXeKF_hw36ejOr4U5PHuQkEifXjcvBdCgyd8BIyq&s=10'],
            ['so', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRcxz3sPuRMoByAxOg9_EQuBeEQK6x1DcUbsWCBH9HFwg&s'],
            ['yd', 'https://images.crunchbase.com/image/upload/c_pad,f_auto,q_auto:eco,dpr_1/307da4ceb53e3089786b'],
            ['ot', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSw99DZ1_VpbuJrxXBOvmmCalpqGBUeGKpdEg&usqp=CAU'],
            ['nkn', 'https://fastsms.su/img/service/nyk.png'],
            ['xt', 'https://fastsms.su/img/service/flp.png'],
            ['ig', 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Instagram_logo_2016.svg/2048px-Instagram_logo_2016.svg.png'],
            ['am', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSH-_j31mFpg7kzUDjGtzBeuQ-4DQX_7aBAaJFBvbtLXeI358dHp43VEhTZ&s=10'],
            ['wr', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRLD7iDoakCpbyXg1rwqDN8I3rxSHO-4ltYS9hcPKewhg&s'],
            ['fb', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQMIUHKeZUSADdjxX7WJVNw3hJk_PbCEcVXtXGcLsdQrQ&s'],
            ['vi', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR_CjocT_DXaqUNDscpPiPZAkY7G0P-v8Y_ZJzbbhCwkQ&s'],
            ['an', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQalmZ0xi4zTN4sywfCE5CKccOGOmNY444uDL1P2AQltoQOJK1pYJHfunI&s=10'],
            ['acc', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcStPuLN8uUGMtYg_CY9PEpXGCUDJwg1VVHDC39Jb1MzxA&s'],
            ['lf', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTe7Xd5KmoXvaQPmaGejf5Cm7yfSp2USKw3461SQzVKUcj6ZOiQ6vOC8Co&s=10'],
            ['82', 'https://www.flaticon.com/free-icon/tinder_4423669?term=tinder&page=1&position=3&origin=search&related_id=4423669'],
        ];

        foreach ($icons as $icon) {
            ServiceIcon::create([
                'short_code' => $icon[0],
                'img_url' => $icon[1],
            ]);
        }
    }
}
