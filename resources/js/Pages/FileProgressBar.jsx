import React, { useState } from "react";
import '../bootstrap';
import '../../css/app.css';
export default function FileProgressBar() {

    const [progress, setProgress] = useState(0)
    const handleButtonClick = () =>{
        if(progress < 100){
            setProgress(progress + 20)
        }
    }
    const handleButtonReset = () => {
        setProgress(0)
    }

    const getColor = () => {
        if(progress < 40){
            return '#ff0000'
        }else if(progress < 70){
            return '#ffa500'
        }else{
            return '#2ecc71'
        }
    }

    return (
        <div className="container">
            <div className="progress-bar">
                <div className="progress-bar-fill" style={{width: `${progress}%`, backgroundColor: getColor() }}></div>
            </div>
            <div className="progress-label">{ progress }%</div>

            <button onClick={ () => handleButtonClick() }>Progress</button>
            <button onClick={ () => handleButtonReset() }>Reset</button>
        </div>
    )
}



// import React, { useState } from 'react';

// export default function FileUploader() {
//   const URL = 'http://localhost:8000/file-progress-bar'
//   const [percentage, setPercentage] = useState(0);

//   function handleFileUpload(event) {
//     const file = event.target.files[0];
//     const formData = new FormData();
//     formData.append('file', file);

//     fetch(URL, {
//       method: 'POST',
//       body: formData,

//       onUploadProgress: (progressEvent) => {
//         setPercentage(Math.round((progressEvent.loaded / progressEvent.total) * 100));
//       }
//     })
//       .then(response => {
//         console.log(response)
//         console.log(response.status);
//       })
//       .catch(error => {
//         console.error(error);
//       });
//   }

//   return (
//     <div>
//       <input type="file" onChange={handleFileUpload} />
//       <progress value={percentage} max="100">
//         {percentage}%
//       </progress>
//     </div>
//   );
// }




// import React from "react";
// // import "./styles.css";

// export default () => {
//   const [file, setFile] = React.useState();
//   const uploadRef = React.useRef();
//   const statusRef = React.useRef();
//   const loadTotalRef = React.useRef();
//   const progressRef = React.useRef();

//   const UploadFile = () => {
//     const file = uploadRef.current.files[0];
//     setFile(URL.createObjectURL(file));
//     var formData = new FormData();
//     formData.append("image", file);
//     var xhr = new XMLHttpRequest();
//     xhr.upload.addEventListener("progress", ProgressHandler, false);
//     xhr.addEventListener("load", SuccessHandler, false);
//     xhr.addEventListener("error", ErrorHandler, false);
//     xhr.addEventListener("abort", AbortHandler, false);
//     xhr.open("POST", "fileupload.php");
//     xhr.send(formData);
//   };

//   const ProgressHandler = (e) => {
//     loadTotalRef.current.innerHTML = `uploaded ${e.loaded} bytes of ${e.total}`;
//     var percent = (e.loaded / e.total) * 100;
//     progressRef.current.value = Math.round(percent);
//     statusRef.current.innerHTML = Math.round(percent) + "% uploaded...";
//   };

//   const SuccessHandler = (e) => {
//     statusRef.current.innerHTML = e.target.responseText;
//     progressRef.current.value = 0;
//   };
//   const ErrorHandler = () => {
//     statusRef.current.innerHTML = "upload failed!!";
//   };
//   const AbortHandler = () => {
//     statusRef.current.innerHTML = "upload aborted!!";
//   };

//   return (
//     <div className="App">
//       <input type="file" name="file" ref={uploadRef} onChange={UploadFile} />
//       <label>
//         File progress: <progress ref={progressRef} value="0" max="100" />
//       </label>
//       <p ref={statusRef}></p>
//       <p ref={loadTotalRef}></p>
//       <img src={file} alt="" style={{ width: "300px", height: "100px" }} />
//     </div>
//   );
// };
