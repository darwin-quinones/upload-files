
import React, { useState } from 'react';
import axios from 'axios';
import '../bootstrap';
//base end point url
const FILE_UPLOAD_BASE_ENDPOINT = "http://127.0.0.1:8020/file-upload";
const URL_UPLOAD = 'http://localhost:8000/upload'
const URL_PROCESS = 'http://localhost:8000/process-file'
export default function ProgressBarExample1() {




    const [progress, setProgress] = useState(0);

    const handleUpload = async (event) => {
        const file = event.target.files[0];
        const formData = new FormData();
        formData.append('file', file);
        console.log('Llegó aquí')
        const response = await axios.post(URL_UPLOAD, formData, {
            onUploadProgress: (progressEvent) => {
                const percentCompleted = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
                setProgress(percentCompleted);
            },
        });

        const { data: fileData } = response;

        await axios.post(URL_PROCESS, { fileId: fileData.id });
    };


    return (
        <div>
            <input type="file" onChange={handleUpload} />
            <div className="progress-bar">
                <div
                    className="progress-bar-fill"
                    style={{ width: `${progress}%` }}
                ></div>
            </div>
        </div>
    );
};


